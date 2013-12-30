<?php
if(!class_exists('ArtMapsNetwork')) {
class ArtMapsNetwork {

    const BlogTableSuffix = 'artmaps_blogs';

    const NetworkConfigPrefix = 'ArtMapsPluginNetworkConfiguration';

    const CoreServerUrlKey = 'CoreServerUrl';

    const MasterKeyKey = 'MasterKey';

    const GoogleMapsApiKeyKey = 'GoogleMapsKey';

    public function getCoreServerUrl() {
        $default = 'http://artmapscore.cloudapp.net';
        $k = ArtMapsNetwork::NetworkConfigPrefix
                . ArtMapsNetwork::CoreServerUrlKey;
        return get_site_option($k, $default, true);
    }

    public function setCoreServerUrl($url) {
        $k = ArtMapsNetwork::NetworkConfigPrefix
                . ArtMapsNetwork::CoreServerUrlKey;
        update_site_option($k, $url);
    }

    public function getMasterKey() {
        $default = '';
        $k = ArtMapsNetwork::NetworkConfigPrefix
                . ArtMapsNetwork::MasterKeyKey;
        return get_site_option($k, $default, true);
    }

    public function setMasterKey($key) {
        $k = ArtMapsNetwork::NetworkConfigPrefix
                . ArtMapsNetwork::MasterKeyKey;
        update_site_option($k, $key);
        $this->createBlogs();
    }

    public function getGoogleMapsKey() {
        $default = '';
        $k = ArtMapsNetwork::NetworkConfigPrefix
                . ArtMapsNetwork::GoogleMapsApiKeyKey;
        return get_site_option($k, $default, true);
    }

    public function setGoogleMapsKey($key) {
        $k = ArtMapsNetwork::NetworkConfigPrefix
                . ArtMapsNetwork::GoogleMapsApiKeyKey;
        update_site_option($k, $key);
        $this->createBlogs();
    }

    public function initialise() {
        global $wpdb;
        $name = $wpdb->base_prefix . self::BlogTableSuffix;
        $sql = "
            CREATE TABLE $name (
            blog_id bigint(20) NOT NULL,
            remote_id bigint(20) NOT NULL,
            name varchar(200) NOT NULL,
            key_as_pem text NOT NULL,
            PRIMARY KEY  (blog_id)
        );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql, true);

        if($this->getMasterKey() != '')
            $this->createBlogs();
    }

    public function createBlog($blogID) {
        require_once('ArtMapsBlog.php');
        global $wpdb;
        $blog = get_blog_details($blogID);
        $table = $wpdb->base_prefix . self::BlogTableSuffix;
        $row = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT * FROM $table WHERE blog_id = %d",
                        $blogID));
        if($row != null) {
            $b = new ArtMapsBlog(
                    $row->blog_id, $row->remote_id, $row->name, $row->key_as_pem);
            $b->initialise();
            return;
        }

        $name = null;
        if(is_subdomain_install()) {
            $name = $blog->domain;
            $name = substr($name, 0, strpos($name, '.'));
        } else {
            $name = $blog->path;
            $name = substr($name, 0, strlen($name) - 1);
            $name = substr($name, strrpos($name, '/') + 1);
        }

        require_once('ArtMapsCoreServer.php');
        $details = ArtMapsCoreServer::registerBlog($name);

        $wpdb->insert($table,
                array(
                        'blog_id' => $blog->blog_id,
                        'remote_id' => $details->ID,
                        'name' => $name,
                        'key_as_pem' => $details->key),
                array('%d', '%d', '%s', '%s'));
        $b = new ArtMapsBlog(
                $blog->blog_id, $details->ID, $name, $details->key);
        $b->initialise();
    }

    public function getBlog($blogID) {
        require_once('ArtMapsBlog.php');
        global $wpdb;
        $table = $wpdb->base_prefix . self::BlogTableSuffix;
        $row = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT * FROM $table WHERE blog_id = %d",
                        $blogID));
        if($row == null)
            throw new ArtMapsBlogNotFoundException();
        return new ArtMapsBlog(
                $row->blog_id, $row->remote_id, $row->name, $row->key_as_pem);
    }

    public function getCurrentBlog() {
        return $this->getBlog(get_current_blog_id());
    }

    private function createBlogs() {
        global $wpdb;
        $table = $wpdb->base_prefix . 'blogs';
        $blogs = $wpdb->get_results("SELECT blog_id FROM $table");
    	foreach($blogs as $blog)
    	    $this->createBlog($blog->blog_id);
    }
}}
?>