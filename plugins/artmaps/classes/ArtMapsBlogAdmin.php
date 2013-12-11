<?php
if(!class_exists('ArtMapsBlogAdmin')) {
class ArtMapsBlogAdmin {

    public function register() {
        $sfx = add_submenu_page(
                'options-general.php',
                'ArtMaps',
                'ArtMaps',
                'manage_options',
                'artmaps-blog-admin-page',
                array($this, 'display'));
        add_action('admin_print_scripts-' . $sfx, function() {
            $p = plugins_url(basename(dirname(dirname(__FILE__))));
            wp_register_script('jquery-ui-complete', "https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js");
            wp_register_script('jquery-ui-timepicker-addon', $p . '/js/lib/jquery-ui-timepicker-addon.js');
            wp_enqueue_script('jquery-ui-complete');
            wp_enqueue_script('jquery-ui-timepicker-addon');
            require_once('ArtMapsNetwork.php');
            $nw = new ArtMapsNetwork();
            $blog = $nw->getCurrentBlog();
            wp_register_style('jquery-theme', $blog->getJQueryThemeUri());
            wp_enqueue_style('jquery-theme');
        });
    }

    public function display() {
        if(!current_user_can('manage_options'))
            wp_die(__('You do not have sufficient permissions to access this page.'));
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        $blog = $nw->getCurrentBlog();
        $updated = $this->checkSubmission($blog);
        require_once('ArtMapsTemplating.php');
        $tpl = new ArtMapsTemplating();
        echo $tpl->renderBlogAdminPage($blog, $updated);
    }

    private function checkSubmission(ArtMapsBlog $blog) {
        $r = false;

        if(isset($_POST['artmaps_blog_config_search_source'])) {
            $blog->setSearchSource(stripslashes(
                    $_POST['artmaps_blog_config_search_source']));
            $r = true;
        }

        if(isset($_POST['artmaps_blog_config_post_author'])) {
            $blog->setPostAuthor(intval(stripslashes(
                    $_POST['artmaps_blog_config_post_author'])));
            $r = true;
        }

        if(isset($_POST['artmaps_blog_config_post_date'])) {
            $blog->setPostDate(stripslashes(
                    $_POST['artmaps_blog_config_post_date']));
            $r = true;
        }

        if(isset($_POST['artmaps_blog_config_post_categories'])) {
            $blog->setPostCategories($_POST['artmaps_blog_config_post_categories']);
            $r = true;
        }

        if(isset($_POST['artmaps_blog_config_jquery_theme_uri'])) {
            $blog->setJQueryThemeUri($_POST['artmaps_blog_config_jquery_theme_uri']);
            $r = true;
        }

        return $r;
    }
}}
?>