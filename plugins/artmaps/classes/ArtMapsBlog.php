<?php
if(!class_exists('ArtMapsBlogNotFoundException')){
class ArtMapsBlogNotFoundException
extends Exception {
    public function __construct($message = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}}

if(!class_exists('ArtMapsBlog')) {
class ArtMapsBlog {

    const BlogConfigPrefix = 'ArtMapsPluginBlogConfiguration';

    const SearchSourceOptionKey = 'SearchSource';

    const PostAuthorOptionKey = 'PostAuthor';

    const PostDateOptionKey = 'PostDate';

    const PostCategoriesOptionKey = 'PostCategories';

    const JQueryThemeUriOptionKey = 'JQueryThemeUri';

    const ObjectPageMapTableSuffix = 'artmaps_object_pages';

    const ImportTableSuffix = 'artmaps_imports';

    private $blogID, $remoteID, $name, $key;

    public function __construct($blogID, $remoteID, $name, $key) {
        $this->blogID = $blogID;
        $this->remoteID = $remoteID;
        $this->name = $name;
        $this->key = $key;
    }

    public function getBlogID() {
        return $this->blogID;
    }

    public function getRemoteID() {
        return $this->remoteID;
    }

    public function getName() {
        return $this->name;
    }

    public function getKey() {
        return $this->key;
    }

    public function initialise() {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;

        $mapDbName = $wpdb->get_blog_prefix($this->blogID) . self::ObjectPageMapTableSuffix;
        $mapDbSql = "
            CREATE TABLE $mapDbName (
            object_id bigint(20) NOT NULL,
            post_id bigint(2) NOT NULL,
            PRIMARY KEY  (object_id)
        );";
        dbDelta($mapDbSql, true);

        $importDbName = $wpdb->get_blog_prefix($this->blogID) . self::ImportTableSuffix;
        $importDbSql = "
        	CREATE TABLE $importDbName (
        	id char(36) NOT NULL,
        	name varchar(255) NOT NULL,
        	status enum('started', 'completed', 'failed') NOT NULL default 'started',
        	starttime datetime NOT NULL,
        	endtime datetime,
        	PRIMARY KEY  (id)
        );";
        dbDelta($importDbSql, true);
    }

    public function getObjectForPage($pageID) {
        global $wpdb;
        $name = $wpdb->get_blog_prefix($this->blogID) . self::ObjectPageMapTableSuffix;
        return $wpdb->get_var(
                $wpdb->prepare(
                        "SELECT object_id FROM $name WHERE post_id = %d",
                        $pageID));
    }

    public function getPageForObject($objectID) {
        global $wpdb;
        $name = $wpdb->get_blog_prefix($this->blogID) . self::ObjectPageMapTableSuffix;
        $pageID = $wpdb->get_var(
                $wpdb->prepare(
                        "SELECT post_id FROM $name WHERE object_id = %d",
                        $objectID));

        if($pageID != null)
            return $pageID;

        require_once('ArtMapsCoreServer.php');
        $core = new ArtMapsCoreServer($this);
        $metadata = $core->fetchObjectMetadata($objectID);
        require_once('ArtMapsTemplating.php');
        $te = new ArtMapsTemplating();
        $title = $te->renderObjectPageTitleTemplate($this, $metadata);
        $content = $te->renderObjectPageTemplate($this, $objectID, $metadata);
        $post = array(
                'comment_status' => get_option('default_comment_status', 'closed'),
                'ping_status' => get_option('default_ping_status', 'closed'),
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_author' => $this->getPostAuthor(),
                'post_type' => 'post',
                'post_date' => $this->getPostDate()
        );
        $pageID = wp_insert_post($post);
        wp_set_post_terms($pageID, $this->getPostCategories(), 'category');
        $wpdb->insert($name,
                    array(
                            'object_id' => $objectID,
                            'post_id' => $pageID),
                    array('%d', '%d'));
        return $pageID;
    }

    public function deletePageObjectMapping($pageID) {
        global $wpdb;
        $name = $wpdb->get_blog_prefix($this->blogID) . self::ObjectPageMapTableSuffix;
        $wpdb->delete($name, array('post_id' => $pageID, '%d'));
    }

    public function getSearchSource() {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::SearchSourceOptionKey;
        return get_blog_option($this->getBlogID(), $k, 'artmaps');
    }

    public function setSearchSource($source) {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::SearchSourceOptionKey;
        update_blog_option($this->getBlogID(), $k, $source);
    }

    public function getPostAuthor() {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::PostAuthorOptionKey;
        return get_blog_option($this->getBlogID(), $k, 1);
    }

    public function setPostAuthor($author) {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::PostAuthorOptionKey;
        update_blog_option($this->getBlogID(), $k, $author);
    }

    public function getPostDate() {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::PostDateOptionKey;
        return get_blog_option($this->getBlogID(), $k, '');
    }

    public function setPostDate($date) {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::PostDateOptionKey;
        update_blog_option($this->getBlogID(), $k, $date);
    }

    public function getPostCategories() {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::PostCategoriesOptionKey;
        return get_blog_option($this->getBlogID(), $k, array());
    }

    public function setPostCategories($categories) {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::PostCategoriesOptionKey;
        update_blog_option($this->getBlogID(), $k, $categories);
    }

    public function getJQueryThemeUri() {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::JQueryThemeUriOptionKey;
        return get_blog_option($this->getBlogID(), $k,
                'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css');
    }

    public function setJQueryThemeUri($uri) {
        $k = ArtMapsBlog::BlogConfigPrefix
                . ArtMapsBlog::JQueryThemeUriOptionKey;
        update_blog_option($this->getBlogID(), $k, $uri);
    }
}}
?>