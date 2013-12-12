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

    public function getObjectForPage($postID) {
        $objectID = get_post_meta($postID, 'object_id', true);
        if(empty($objectID))
            return null;
        return $objectID;
    }

    public function getPageIDForObject($objectID) {
        $query = new WP_Query("post_type=artwork&meta_key=object_id&meta_value=$objectID");
        while($query->have_posts()) {
            $query->the_post();
            return get_the_ID();
        }
        return null;
    }

    public function getPageForObject($objectID) {
        $lock = "object_generation_$objectID";
        $i = 0;
        while(apc_exists($lock) && $i < 10) {
            usleep(500000);
            $i++;
        }
        apc_add($lock, '$');
        $pageID = null;
        try {
            $pageID = $this->getPageForObjectInt($objectID);
        } catch(Exception $e) { }
        apc_delete($lock);
        return $pageID;
    }

    private function getPageForObjectInt($objectID) {

        $pageID = $this->getPageIDForObject($objectID);
        if($pageID != null)
            return $pageID;

        require_once('ArtMapsCoreServer.php');
        $core = new ArtMapsCoreServer($this);
        $metadata = $core->fetchObjectMetadata($objectID);

        if($metadata == -1)
            return null;

        require_once('ArtMapsTemplating.php');
        $te = new ArtMapsTemplating();

        $post = array(
                'comment_status' => get_option('default_comment_status', 'closed'),
                'ping_status' => get_option('default_ping_status', 'closed'),
                'post_title' => $te->renderObjectPageTitleTemplate($this, $metadata),
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => $this->getPostAuthor(),
                'post_type' => 'artwork',
                'post_date' => $this->getPostDate()
        );
        $pageID = wp_insert_post($post);
        update_post_meta($pageID, 'object_id', $objectID);
        foreach($metadata as $key => $value) {
            update_post_meta($pageID, $key, $value);
        }
        wp_set_post_terms($pageID, $this->getPostCategories(), 'category');
        return $pageID;
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