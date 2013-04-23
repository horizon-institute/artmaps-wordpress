<?php
if(class_exists('ArtMapsNetwork')) {
add_action('init', function() {
    $network = new ArtMapsNetwork();
    wp_register_script('google-jsapi',
            'https://www.google.com/jsapi?key=' . $network->getGoogleMapsKey());
    wp_register_script('google-maps',
            'https://maps.google.com/maps/api/js?libraries=places&sensor=true&key=' . $network->getGoogleMapsKey());
    wp_register_script('jquery-xcolor', get_stylesheet_directory_uri() . '/js/lib/jquery.xcolor.min.js');
    wp_register_script('jquery-outside-event', get_stylesheet_directory_uri() . '/js/lib/jquery.ba-outside-events.min.js');
    wp_register_script('markerclusterer', get_stylesheet_directory_uri() . '/js/lib/markerclusterer.js');
    wp_register_script('jquery-bbq', get_stylesheet_directory_uri() . '/js/lib/jquery.ba-bbq.min.js');
    wp_register_script('styledmarker', get_stylesheet_directory_uri() . '/js/lib/styledmarker.js');
    wp_register_script('jquery-ui-complete', "https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js");

    wp_register_script('artmaps-map', get_stylesheet_directory_uri() . '/js/map/artmaps.js.php');
    wp_register_script('artmaps-object', get_stylesheet_directory_uri() . '/js/object/artmaps.js.php');

    wp_register_style('jquery-theme', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css');

    if(is_user_logged_in())
        wp_register_style('artmaps-template-general', get_stylesheet_directory_uri() . '/css/template-general-loggedin.css');
    else
        wp_register_style('artmaps-template-general', get_stylesheet_directory_uri() . '/css/template-general-loggedout.css');
    wp_register_style('artmaps-template-map', get_stylesheet_directory_uri() . '/css/template-map.css');
    wp_register_style('artmaps-template-object', get_stylesheet_directory_uri() . '/css/template-object.css');
});

add_action('login_head', function() {
    echo '<link type="text/css" rel="StyleSheet" href="'
            . get_stylesheet_directory_uri() . '/css/login.css" />';
});

add_action('template_redirect', function() {
    global $wp;
    if(stripos($wp->request, 'map') === 0) {
        header("HTTP/1.1 200 OK");
        load_template(locate_template('template-map.php'));
        exit;
    }
}, 0);

add_filter('wp_redirect', function($url, $status = 200) {
    if(stripos(parse_url($url, PHP_URL_PATH), 'wp-signup.php') > -1)
        return site_url('wp-login.php');
    return $url;
});

add_action('signup_header', function(){
    wp_redirect(site_url('wp-login.php'));
    exit;
});

add_filter('gettext', function($v) {
    if($v == 'Or click your account provider:')
        return 'Select your account provider:';
    if($v == 'Or login using an OpenID')
        return 'Or manually enter your OpenID:';
    return $v;
}, 999);

add_action('profile_update', function() {
    if(!array_key_exists('artmaps_redirect', $_POST))
        return;
    $r = $_POST['artmaps_redirect'];
    if($r != '') {
        wp_redirect($r);
        exit;
    }
});
} else {
	error_log('The ArtMaps plugin is either not present or has not been activated.');
}
?>
