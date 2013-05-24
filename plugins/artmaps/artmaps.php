<?php
/*
Plugin Name: ArtMaps
Plugin URI: http://www.horizon.ac.uk/
Version: v0.2.0
Author: <a href="http://www.horizon.ac.uk/">Horizon Digital Economy Research</a>
Description: Plugin providing ArtMaps functionality to WordPress.
*/
if(!class_exists('ArtMapsCore')) {
class ArtMapsCore {
    public function onActivation() {
        global $wp_rewrite;
        if(!isset($wp_rewrite))
            $wp_rewrite = new WP_Rewrite();
        $wp_rewrite->flush_rules();

        require_once('classes/ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        $nw->initialise();
    }
}}

if(class_exists('ArtMapsCore') && !isset($ArtMapsCore)) {
    global $ArtMapsCore;
    $ArtMapsCore = new ArtMapsCore();

    register_activation_hook(__FILE__,  function() {
        global $ArtMapsCore;
        $ArtMapsCore->onActivation();
    });

    add_action('init', function() {
        $wropt = 'ArtMapsPluginRewriteRulesGenerated';
        if(!get_option($wropt, false)) {
            global $wp_rewrite;
            if(!isset($wp_rewrite))
                $wp_rewrite = new WP_Rewrite();
            $wp_rewrite->flush_rules();
            update_option($wropt, true);
        }
    });

    add_filter('query_vars', function($vars) {
        $vars[] = 'objectid';
        return $vars;
    });

    add_action('generate_rewrite_rules', function($wpRewrite) {
        $rules = array(
            'object/(\d+)/?' => 'index.php?objectid=$matches[1]'
        );
        $wpRewrite->rules = $rules + $wpRewrite->rules;
    });

    add_action('parse_request', function($wp) {
        if(!array_key_exists('objectid', $wp->query_vars))
            return;
        $objectID = $wp->query_vars['objectid'];
        require_once('classes/ArtMapsNetwork.php');
        $n = new ArtMapsNetwork();
        $b = $n->getCurrentBlog();
        $pageID = $b->getPageForObject($objectID);
        $wp->query_vars['page_id'] = $pageID;
    });

    add_action('wpmu_new_blog', function($blogID) {
        require_once('classes/ArtMapsNetwork.php');
        $n = new ArtMapsNetwork();
        $n->createBlog($blogID);
    });

    add_action('network_admin_menu', function() {
        require_once('classes/ArtMapsNetworkAdmin.php');
        $networkAdmin = new ArtMapsNetworkAdmin();
        $networkAdmin->register();
    });

    add_action('admin_menu', function() {
        require_once('classes/ArtMapsBlogAdmin.php');
        $blogAdmin = new ArtMapsBlogAdmin();
        $blogAdmin->register();
        require_once('classes/ArtMapsImportAdmin.php');
        $importAdmin = new ArtMapsImportAdmin();
        $importAdmin->register();
    });

    add_filter('xmlrpc_methods', function($methods) {
        require_once('classes/ArtMapsXmlRpc.php');
        $svc = new ArtMapsXmlRpc();
        $methods['artmaps.commentTemplate'] = array($svc, 'generateCommentTemplate');
        $methods['pingback.ping'] = array($svc, 'doPingback');
        $methods['artmaps.fetchComments'] = array($svc, 'fetchComments');
        return $methods;
    });

    add_action('wp_ajax_artmaps.publishComment', function() {
        require_once('classes/ArtMapsAjax.php');
        $ajax = new ArtMapsAjax();
        header('Content-Type: application/json');
        echo $ajax->publishComment(
                $_POST['objectID'],
                stripslashes($_POST['text']));
        exit;
    });

    add_action('wp_ajax_artmaps.generateCommentTemplate', function() {
        require_once('classes/ArtMapsAjax.php');
        $ajax = new ArtMapsAjax();
        header('Content-Type: application/json');
        echo $ajax->generateCommentTemplate($_POST['objectID']);
        exit;
    });

    add_action('wp_ajax_artmaps.createDraftComment', function() {
        require_once('classes/ArtMapsAjax.php');
        $ajax = new ArtMapsAjax();
        header('Content-Type: application/json');
        echo $ajax->createDraftComment($_POST['objectID']);
        exit;
    });

    add_action('wp_ajax_artmaps.signData', function() {
        require_once('classes/ArtMapsAjax.php');
        $ajax = new ArtMapsAjax();
        header('Content-Type: application/json');
        $data = stripslashes_deep($_POST['data']);
        echo $ajax->signData($data);
        exit;
    });

    require_once('classes/ArtMapsNetwork.php');
    require_once('classes/ArtMapsCoreServer.php');
    require_once('classes/ArtMapsTemplating.php');
    require_once('classes/ArtMapsUser.php');
}
?>
