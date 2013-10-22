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

    public function isConnected() {
        require_once('classes/ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        return $nw->getMasterKey() != '';
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

        global $ArtMapsCore;

        if(!is_admin() && $ArtMapsCore->isConnected()) {
            require_once('classes/ArtMapsContent.php');
            $content = new ArtMapsContent();
            $content->init();
        }

        if(!wp_next_scheduled('artmaps_generate_digest')) {
            wp_schedule_event(time(), 'daily', 'artmaps_generate_digest');
        }
    });

    add_filter('query_vars', function($vars) {
        $vars[] = 'objectid';
        $vars[] = 'importid';
        return $vars;
    });

    add_action('generate_rewrite_rules', function($wpRewrite) {
        $rules = array(
            'object/(\d+)/?' => 'index.php?objectid=$matches[1]',
            'import/(.+)/?' => 'index.php?importid=$matches[1]'
        );
        $wpRewrite->rules = $rules + $wpRewrite->rules;
    });

    add_action('parse_request', function($wp) {
        if(array_key_exists('objectid', $wp->query_vars)) {
            $objectID = $wp->query_vars['objectid'];
            require_once('classes/ArtMapsNetwork.php');
            $n = new ArtMapsNetwork();
            $b = $n->getCurrentBlog();
            $pageID = $b->getPageForObject($objectID);
            global $wp_query;
            $wp_query = new WP_Query('p=' . $pageID);
        } else if(array_key_exists('importid', $wp->query_vars)) {
            $importID = $wp->query_vars['importid'];
            require_once('classes/ArtMapsNetwork.php');
            $n = new ArtMapsNetwork();
            $b = $n->getCurrentBlog();
            $headers = getallheaders();
            require_once('classes/ArtMapsImport.php');
            $import = ArtMapsImport::fromID($b, $importID);
            if($import != null) {
                if($headers['success'] === 'false') {
                    $import->setFailed();
                } else {
                    $import->setCompleted();
                }
                header(' ', true, 200);
            } else {
                header(' ', true, 404);
            }
            die;
        }
    });

    add_action('wpmu_new_blog', function($blogID) {
        require_once('classes/ArtMapsNetwork.php');
        $n = new ArtMapsNetwork();
        $n->createBlog($blogID);
    });

    add_action('delete_post', function($postID) {
        require_once('classes/ArtMapsNetwork.php');
        require_once('classes/ArtMapsBlog.php');
        $nw = new ArtMapsNetwork();
        $blog = $nw->getCurrentBlog();
        $blog->deletePageObjectMapping($postID);
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

    add_action('wp_ajax_artmaps.signData', function() {
        require_once('classes/ArtMapsAjax.php');
        $ajax = new ArtMapsAjax();
        header('Content-Type: application/json');
        $data = stripslashes_deep($_POST['data']);
        if(array_key_exists('URI', $data)
                && strpos($data['URI'], 'finalisation') === 0) {
            require_once('classes/ArtMapsUser.php');
            $user = ArtMapsUser::currentUser();
            if(!user_can($user->getID(), 'contributor')) {
                header("HTTP/1.1 403 Forbidden");
                exit;
            }
        }
        echo $ajax->signData($data);
        exit;
    });

    function storeMapState() {
        require_once('classes/ArtMapsAjax.php');
        $ajax = new ArtMapsAjax();
        header('Content-Type: application/json');
        echo $ajax->storeMapState($_POST['data']['state']);
        exit;
    }
    add_action('wp_ajax_artmaps.storeMapState', 'storeMapState');
    add_action('wp_ajax_nopriv_artmaps.storeMapState', 'storeMapState');

    add_action('wp_ajax_artmaps.deleteComment', function() {
        require_once('classes/ArtMapsAjax.php');
        $ajax = new ArtMapsAjax();
        header('Content-Type: application/json');
        $commentID = stripslashes_deep($_POST['commentID']);
        echo $ajax->deleteComment($commentID);
        exit;
    });

    add_action('pre_get_posts', function($query) {
        require_once('classes/ArtMapsSearch.php');
        $s = new ArtMapsSearch();
        return $s->preSearch($query);
    });

    add_filter('the_posts', function($posts) {
        require_once('classes/ArtMapsSearch.php');
        $s = new ArtMapsSearch();
        return $s->search($posts);
    });

    add_filter('the_content', function($content) {
        require_once('classes/ArtMapsContent.php');
        $c = new ArtMapsContent();
        return $c->parse($content);
    });

    add_action('comment_post', function($id) {
        if(array_key_exists('artmaps-location-id', $_POST)) {
            $comment = get_comment($id);
            require_once('classes/ArtMapsNetwork.php');
            $n = new ArtMapsNetwork();
            $b = $n->getCurrentBlog();
            $objectID = $b->getObjectForPage($comment->comment_post_ID );
            require_once('classes/ArtMapsCoreServer.php');
            $c = new ArtMapsCoreServer($b);
            $c->linkComment($id, intval($_POST['artmaps-location-id']), $objectID);
        }
    });

    add_action('artmaps_generate_digest', function() {
        require_once('classes/ArtMapsDigest.php');
        require_once('classes/ArtMapsNetwork.php');
        require_once('classes/ArtMapsCoreServer.php');
        $nw = new ArtMapsNetwork();
        $blog = $nw->getCurrentBlog();
        $digest = new ArtMapsDigest();
        $digest->sendDigestEmail();
    });

    add_action ('admin_menu', function() {
        add_meta_box ('artmaps_location' , 'ArtMaps Location' , function($post) {
            echo "ArtMaps";
        }, 'post' , 'normal' , 'high' );
    });

    add_action ( 'save_post' , function() {

    });
}
?>
