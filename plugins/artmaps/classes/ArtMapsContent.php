<?php
if(!class_exists('ArtMapsContent')) {
class ArtMapsContent {

    public function init() {

        // Do not load content on login/registration page
        if(in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php')))
            return;

        require_once('ArtMapsNetwork.php');
        require_once('ArtMapsCoreServer.php');
        require_once('ArtMapsUtil.php');
        require_once('ArtMapsUser.php');
        $network = new ArtMapsNetwork();
        $blog = $network->getCurrentBlog();
        $p = plugins_url(basename(dirname(dirname(__FILE__))));
        wp_register_script('google-jsapi', 'https://www.google.com/jsapi?key=' . $network->getGoogleMapsKey());
        wp_register_script('google-maps', 'https://maps.google.com/maps/api/js?libraries=places,geometry&sensor=true&key=' . $network->getGoogleMapsKey());
        wp_register_script('styledmarker', $p . '/js/lib/styledmarker.js');
        wp_register_script('jquery-xcolor', $p . '/js/lib/jquery.xcolor.min.js');
        wp_register_script('jquery-outside-event', $p . '/js/lib/jquery.ba-outside-events.min.js');
        wp_register_script('jquery-bbq', $p . '/js/lib/jquery.ba-bbq.min.js');
        wp_register_script('jquery-cookie', $p . '/js/lib/jquery.cookie.js');
        wp_register_script('jquery-ui-complete', "https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js");
        wp_register_script('jquery-ui-timepicker-addon', $p . '/js/lib/jquery-ui-timepicker-addon.js');
        wp_register_script('jquery-timeago', $p . '/js/lib/jquery.timeago.js');
        wp_register_script('jquery-scrollto', $p . '/js/lib/jquery.scrollTo.min.js');
        wp_register_script('fancybox', $p . '/js/lib/fancybox/jquery.fancybox.pack.js');
        wp_register_script('markerclusterer', $p . '/js/lib/markerclusterer.js');
        wp_register_script('infobox', $p . '/js/lib/infobox.js');
        wp_register_script('geo', $p . '/js/lib/geo.js');
        wp_register_script('latlon', $p . '/js/lib/latlon.js');
        wp_register_script('artmaps-base', $p . '/js/base.js');
        wp_register_script('artmaps-util', $p . '/js/util.js');
        wp_register_script('artmaps-map-ui', $p . '/js/map-ui.js');
        wp_register_script('artmaps-map', $p . '/js/map.js');
        wp_register_script('artmaps-object-ui', $p . '/js/object-ui.js');
        wp_register_script('artmaps-object', $p . '/js/object.js');
        wp_register_script('artmaps-login', $p . '/js/login.js', false, false, true);
        wp_register_script('artmaps-comment-date', $p . '/js/comment-date.js');
        wp_register_style('artmaps', ArtMapsUtil::findThemeUri('css/artmaps.css'));
        wp_register_style('artmaps-map', ArtMapsUtil::findThemeUri('css/map.css'));
        wp_register_style('artmaps-object', ArtMapsUtil::findThemeUri('css/object.css'));
        foreach(array(
                        'google-jsapi', 'google-maps', 'jquery', 'jquery-ui-complete',
                        'jquery-bbq', 'jquery-xcolor', 'jquery-timeago', 'jquery-scrollto',
                        'json2', 'markerclusterer', 'infobox', 'styledmarker',
                        'geo', 'latlon', 'artmaps-base', 'artmaps-util',
                        'artmaps-map-ui', 'artmaps-map', 'artmaps-object-ui', 'artmaps-object',
                        'artmaps-login', 'artmaps-comment-date', 'fancybox')
                as $script)
            wp_enqueue_script($script);
        foreach(array('jquery-theme', 'artmaps', 'artmaps-map', 'artmaps-object') as $style)
            wp_enqueue_style($style);
        $core = new ArtMapsCoreServer($blog);

        $user = ArtMapsUser::currentUser();

        $gravatar_hash = md5('unknown');
        if(is_user_logged_in()) {
            $gravatar_hash = md5(strtolower(trim($user->getEmail())));
        }
        session_start();
        wp_localize_script('artmaps-base', 'ArtMapsConfig',
                array(
                        'CoreServerPrefix' => $core->getPrefix(),
                        'SiteUrl' => site_url(),
                        'SearchUrl' => get_search_link(),
                        'PluginDirUrl' => $p,
                        'ClusterIconUrl' => ArtMapsUtil::findThemeUri('content/cluster.png'),
                        'MyLocationIconUrl' => ArtMapsUtil::findThemeUri('content/mylocation.gif'),
                        'LoadingIcon50x50Url' => ArtMapsUtil::findThemeUri('content/loading/50x50.gif'),
                        'LoadingIcon25x25Url' => ArtMapsUtil::findThemeUri('content/loading/25x25.gif'),
                        'AjaxUrl' => admin_url('admin-ajax.php', is_ssl() ? 'https' : 'http'),
                        'IsUserLoggedIn' => is_user_logged_in(),
                        'CoreUserID' => is_user_logged_in() ? $user->getCoreID($blog) : -1,
                        'MapState' => isset($_SESSION['mapState']) ? $_SESSION['mapState'] : false,
                        'UserLevel' => is_user_logged_in() ? $user->getRoles() : array(),
                        'AvatarUrl32' => (is_ssl() ? 'https' : 'http') . '://gravatar.com/avatar/' . $gravatar_hash . '?d=mystery&s=32'
                ));
    }

    public function parse($content) {
        require_once('ArtMapsNetwork.php');
        require_once('ArtMapsTemplating.php');
        $n = new ArtMapsNetwork();
        $te = new ArtMapsTemplating();
        if(strpos($content, '[artmap]') !== false)
            $content = str_replace('[artmap]',
                    $te->renderMainMapTemplate($n->getCurrentBlog()), $content);
        return $content;
    }

}};
