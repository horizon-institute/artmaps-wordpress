<?php
if(class_exists('ArtMapsNetwork')) {

add_action('init', function() {
    $network = new ArtMapsNetwork();
    $p = get_stylesheet_directory_uri();
    wp_register_script('google-jsapi', 'https://www.google.com/jsapi?key=' . $network->getGoogleMapsKey());
    wp_register_script('google-maps', 'https://maps.google.com/maps/api/js?libraries=places&sensor=true&key=' . $network->getGoogleMapsKey());
    wp_register_script('styledmarker', $p . '/js/lib/styledmarker.js');
    wp_register_script('jquery-xcolor', $p . '/js/lib/jquery.xcolor.min.js');
    wp_register_script('jquery-outside-event', $p . '/js/lib/jquery.ba-outside-events.min.js');
    wp_register_script('jquery-bbq', $p . '/js/lib/jquery.ba-bbq.min.js');
    wp_register_script('jquery-cookie', $p . '/js/lib/jquery.cookie.js');
    wp_register_script('jquery-ui-complete', "https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js");
    wp_register_script('markerclusterer', $p . '/js/lib/markerclusterer.js');
    wp_register_script('artmaps-map', $p . '/js/map/artmaps.js.php');
    wp_register_script('artmaps-object', $p . '/js/object/artmaps.js.php');
    wp_register_style('jquery-theme', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css');
    wp_register_style('artmaps-template-map', $p . '/css/template-map.css');
    wp_register_style('artmaps-template-object', $p . '/css/template-object.css');
    wp_register_style('artmaps-login', $p . '/css/login.css');
});

add_action('template_redirect', function() {
    if(is_home()) {
        wp_redirect(get_site_url() . '/map', 301);
        exit;
    }
    global $wp;
    if(stripos($wp->request, 'map') === 0) {
        status_header(200);
        load_template(locate_template('template-map.php'));
        exit;
    }
}, 0);

add_action('login_head', function() {
    require_once('login-head.php');
});

add_action('login_form', function() {
    require_once('login-form.php');
});

add_filter('login_redirect', function($redirect) {
    if($redirect != null && $redirect != '' && strpos($redirect, 'wp-admin') < 0)
        return $redirect;
    return site_url() . '/map';
});

remove_action('wp_head', '_admin_bar_bump_cb');

add_filter('show_admin_bar', '__return_false');

} else {
	error_log('This theme requires that the ArtMaps plugin be installed and activated.');
    status_header(404);
    load_template(locate_template('404.php'));
    exit;
}
?>
