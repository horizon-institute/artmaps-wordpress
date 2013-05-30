<?php
if(class_exists('ArtMapsNetwork')) {

function themeUri($path = '') {
    return get_stylesheet_directory_uri() . $path;
}

add_action('init', function() {
    $network = new ArtMapsNetwork();
    wp_register_script('google-jsapi', 'https://www.google.com/jsapi?key=' . $network->getGoogleMapsKey());
    wp_register_script('google-maps', 'https://maps.google.com/maps/api/js?libraries=places&sensor=true&key=' . $network->getGoogleMapsKey());
    wp_register_script('styledmarker', themeUri('/js/lib/styledmarker.js'));
    wp_register_script('jquery-xcolor', themeUri('/js/lib/jquery.xcolor.min.js'));
    wp_register_script('jquery-outside-event', themeUri('/js/lib/jquery.ba-outside-events.min.js'));
    wp_register_script('jquery-bbq', themeUri('/js/lib/jquery.ba-bbq.min.js'));
    wp_register_script('jquery-cookie', themeUri('/js/lib/jquery.cookie.js'));
    wp_register_script('jquery-ui-complete', "https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js");
    wp_register_script('markerclusterer', themeUri('/js/lib/markerclusterer.js'));
    wp_register_script('artmaps-map', themeUri('/js/map/artmaps.js.php'));
    wp_register_script('artmaps-object', themeUri('/js/object/artmaps.js.php'));
    wp_register_style('jquery-theme', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css');
    wp_register_style('artmaps-template-general', themeUri('/css/template-general.css'));
    wp_register_style('artmaps-template-map', themeUri('/css/template-map.css'));
    wp_register_style('artmaps-template-object', themeUri('/css/template-object.css'));
});

add_action('template_redirect', function() {
    if(is_home()) {
        wp_redirect("map", 301);
        exit;
    }
    global $wp;
    if(stripos($wp->request, 'map') === 0) {
        header("HTTP/1.1 200 OK");
        load_template(locate_template('template-map.php'));
        exit;
    }
}, 0);

} else {
	error_log('The ArtMaps plugin is either not present or has not been activated.');
}
?>
