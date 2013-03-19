<?php
if(!class_exists('ArtMapsExternalBlog')) {
class ArtMapsExternalBlog {
    public $isConfigured = false;
    public $url, $username, $password;
}}

if(!class_exists('ArtMapsUserNotFoundException')){
class ArtMapsUserNotFoundException
extends Exception {
    public function __construct($message = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}}

if(!class_exists('ArtMapsNullUserException')){
class ArtMapsNullUserException
extends Exception {
    public function __construct($message = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
}

if(!class_exists('ArtMapsUser')) {
class ArtMapsUser {

    const ExternalBlogConfigKey = 'artmaps_external_blog_config';

    private $wpUser;

    public function __construct(WP_User $wpUser) {
        $this->wpUser = $wpUser;
    }

    public static function currentUser() {
        if(is_user_logged_in())
            return new ArtMapsUser(wp_get_current_user());
        return new ArtMapsNullUser();
    }

    public static function fromID($id) {
        return ArtMapsUser::from('id', $id);
    }

    public static function fromSlug($slug) {
        return ArtMapsUser::from('slug', $slug);
    }

    public static function fromEmail($email) {
        return ArtMapsUser::from('email', $email);
    }

    public static function fromLogin($login) {
        return ArtMapsUser::from('login', $login);
    }

    private static function from($field, $value) {
        $u = get_user_by($field, $value);
        if($u === false)
            throw new ArtMapsUserNotFoundException(
                    "User with $field '$value' does not exist");
        return new ArtMapsUser($u);
    }

    public function getID() {
        return $this->wpUser->ID;
    }

    public function getLogin() {
        return $this->wpUser->user_login;
    }

    public function getRoles() {
        return $this->wpUser->roles;
    }

    public function getExternalBlog() {
        $cfg = get_user_meta($this->getID(), self::ExternalBlogConfigKey, true);
        if($cfg == null || $cfg == '') {
            $b = new ArtMapsExternalBlog();
            $b->isConfigured = false;
            return $b;
        }

        $b = new ArtMapsExternalBlog();
        $b->isConfigured = true;
        $b->url = $cfg['url'];
        $b->username = $cfg['username'];
        $b->password = $cfg['password'];
        return $b;
    }

    public function hasOpenID() {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if(!is_plugin_active('openid/openid.php'))
            return false;
        global $wpdb;
        $res = $wpdb->get_var(
                $wpdb->prepare(
                        'SELECT COUNT(1) FROM '
                        . openid_identity_table()
                        . ' WHERE user_id = %s',
                        $this->getID()));
        return $res != 0;
    }

    public function displayProfileFields() {
        require_once('ArtMapsTemplating.php');
        $tpl = new ArtMapsTemplating();
        $redirect = array_key_exists('artmaps_redirect', $_GET)
                ? $_GET['artmaps_redirect']
                : '';
        echo $tpl->renderUserProfileFields(
                $this->getExternalBlog(),
                $redirect);
    }

    public function updateProfileFields() {
        if(!current_user_can('edit_user', $this->getID()))
            wp_die(__('You do not have sufficient permissions to access this page.'));

        $cfg = get_user_meta($this->getID(), self::ExternalBlogConfigKey, true);

        if(array_key_exists('artmaps_use_personal_blog_url', $_POST)) {
            $url = trim($_POST['artmaps_use_personal_blog_url']);
            if($url) {
                if(strpos($url, "http") !== 0)
                    $url = 'http://' . $url;
                if(strrpos($url, '/') == strlen($url) - 1)
                    $url = substr($url, 0, strlen($url) - 1);
                $cfg['url'] = $url;
            }
        }

        if(array_key_exists('artmaps_use_personal_blog_username', $_POST)) {
            $un = trim($_POST['artmaps_use_personal_blog_username']);
            if($un)
                $cfg['username'] = $un;
        }

        if(array_key_exists('artmaps_use_personal_blog_password', $_POST)) {
            $pw = trim($_POST['artmaps_use_personal_blog_password']);
            if($pw)
                $cfg['password'] = $pw;
        }

        update_user_meta($this->getID(), self::ExternalBlogConfigKey, $cfg);
    }

    public function getCoreID(ArtMapsBlog $blog) {
        require_once('ArtMapsCoreServer.php');
        $cs = new ArtMapsCoreServer($blog);
        return $cs->fetchCoreUserID($this);
    }
}}

if(!class_exists('ArtMapsNullUser')) {
class ArtMapsNullUser
extends ArtMapsUser {

    public function __construct() { }

    public function getID() {
        throw new ArtMapsNullUserException();
    }

    public function getLogin() {
        throw new ArtMapsNullUserException();
    }

    public function getRoles() {
        throw new ArtMapsNullUserException();
    }

    public function getExternalBlog() {
        throw new ArtMapsNullUserException();
    }

    public function hasOpenID() {
        throw new ArtMapsNullUserException();
    }

    public function displayProfileFields() {
        throw new ArtMapsNullUserException();
    }

    public function updateProfileFields() {
        throw new ArtMapsNullUserException();
    }

    public function getCoreID(ArtMapsBlog $blog) {
        throw new ArtMapsNullUserException();
    }
}}
?>