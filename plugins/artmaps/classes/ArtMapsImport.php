<?php
require_once('UUID.php');
use RobotSnowfall\UUID;
if(!class_exists('ArtMapsImport')) {
class ArtMapsImport {

	const TimeFormat = 'Y-m-d H:i:s';

	private function __construct() {}

	public $blog, $ID, $name, $status, $starttime, $endtime;

	/*id        | char(36)                             | NO   | PRI | NULL    |       |
	| name      | varchar(255)                         | NO   |     | NULL    |       |
	| status    | enum('started','completed','failed') | NO   |     | started |       |
	| endtime   | datetime                             | YES  |     | NULL    |       |
	| starttime*/

	public function getID() { return $this->ID; }
	public function getName() { return $this->name; }
	public function getStatus() { return $this->status; }
	public function getStartTime() { return $this->starttime; }
	public function getEndTime() { return $this->endtime; }

	public static function createNew(ArtMapsBlog $blog, $file, $name) {
		$import = new self();
		$import->blog = $blog;
		$import->ID = (string)UUID::v4();
		$import->status = 'started';
		$import->name = $name;
		$import->starttime = time();
		$import->endtime = null;

		global $wpdb;
		$name = $wpdb->get_blog_prefix($blog->getBlogID()) . ArtMapsBlog::ImportTableSuffix;
		$wpdb->insert($name,
				array(
						'id' => $import->ID,
						'name' => $import->name,
						'status' => $import->status,
						'starttime' => date(self::TimeFormat, $import->starttime),
						'endtime' => $import->endtime),
				array('%s', '%s', '%s', '%s', '%s'));

		require_once('ArtMapsCrypto.php');
		$crypto = new ArtMapsCrypto();
		$sig = $crypto->signFile(
				$file,
				$blog->getKey());
		require_once('ArtMapsCoreServer.php');
		$cs = new ArtMapsCoreServer($blog);
		$cs->doImport($file, $sig, $import->ID);

		return $import;
	}

	public static function fromID(ArtMapsBlog $blog, $ID) {
		$o = new ArtMapsImport();


		global $wpdb;
		$name = $wpdb->get_blog_prefix($blog->getBlogID()) . ArtMapsBlog::ImportTableSuffix;
		$d = $wpdb->get_row($wpdb->prepare("SELECT * FROM $name WHERE ID = %s", $ID ));
		if($d == null)
			return $null;

		$o->blog = $blog;
		$o->ID = $d->id;
		$o->name = $d->name;
		$o->status = $d->status;
		$o->starttime = $d->starttime;
		$o->endtime = $d->endtime;

		return $o;
	}

	public static function all(ArtMapsBlog $blog) {
		$all = array();

		global $wpdb;
		$name = $wpdb->get_blog_prefix($blog->getBlogID()) . ArtMapsBlog::ImportTableSuffix;
		$results = $wpdb->get_results("SELECT * FROM $name SORT BY starttime DESC");
		foreach($results as $row) {
			$o = new self();
			$o->ID = $row->id;
			$o->name = $row->name;
			$o->status = $row->status;
			$o->starttime = $row->starttime;
			$o->endtime = $row->endtime;
			array_push($all, $o);
		}

		return $all;
	}

	public function setCompleted() {
	    global $wpdb;
	    $name = $wpdb->get_blog_prefix($this->blog->getBlogID()) . ArtMapsBlog::ImportTableSuffix;
	    $wpdb->update($name,
	            array('status' => 'completed', 'endtime' => date(self::TimeFormat, time())) ,
	            array('id' => $this->ID));
	}

	public function setFailed() {
	    global $wpdb;
	    $name = $wpdb->get_blog_prefix($this->blog->getBlogID()) . ArtMapsBlog::ImportTableSuffix;
	    $wpdb->update($name,
	            array('status' => 'failed', 'endtime' => date(self::TimeFormat, time())) ,
	            array('id' => $this->ID));
	}

}}
?>