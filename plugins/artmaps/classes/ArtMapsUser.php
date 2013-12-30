<?php
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
}}

if(!class_exists('ArtMapsUserCreationException')){
class ArtMapsUserCreationException
extends Exception {
    public function __construct($message = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}}

if(!class_exists('ArtMapsUser')) {
class ArtMapsUser {

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

    public static function create($username, $password, $email) {
        $id = wp_create_user($username, $password, $email);
        if(is_wp_error($id))
            throw new ArtMapsUserCreationException($id->get_error_message());
        return ArtMapsUser::fromID($id);
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

    public function getDisplayName() {
        return $this->wpUser->display_name;
    }

    public function getEmail() {
        return $this->wpUser->user_email;
    }

    public function setDisplayName($displayName) {
        $this->wpUser->user_nicename = $displayName;
        $this->wpUser->display_name = $displayName;
        wp_update_user(array('ID' => $this->wpUser->ID, 'user_nicename' => $displayName));
        wp_update_user(array('ID' => $this->wpUser->ID, 'display_name' => $displayName));
    }

    public function getBlogUrl() {
        return $this->wpUser->user_url;
    }

    public function setBlogUrl($url) {
        $this->wpUser->user_url = $url;
        wp_update_user(array( 'ID' => $this->wpUser->ID, 'user_url' => $url));
    }

    public function getRoles() {
        return $this->wpUser->roles;
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

    public function hasOpenID() {
        throw new ArtMapsNullUserException();
    }

    public function getCoreID(ArtMapsBlog $blog) {
        throw new ArtMapsNullUserException();
    }
}}
?>