<?php
if(!class_exists('ArtMapsBlogAdmin')) {
class ArtMapsBlogAdmin {

    public function register() {
        add_submenu_page(
                'options-general.php',
                'ArtMaps',
                'ArtMaps',
                'manage_options',
                'artmaps-blog-admin-page',
                array($this, 'display'));
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

        if(isset($_POST['artmaps_blog_option_comment_template'])) {
            $blog->setCommentTemplate(stripslashes(
                    $_POST['artmaps_blog_option_comment_template']));
            $r = true;
        }

        if(isset($_POST['artmaps_blog_option_object_page_title_template'])) {
            $blog->setObjectPageTitleTemplate(stripslashes(
                    $_POST['artmaps_blog_option_object_page_title_template']));
            $r = true;
        }
        
        if(isset($_POST['artmaps_blog_config_search_source'])) {
            $blog->setSearchSource(stripslashes(
                    $_POST['artmaps_blog_config_search_source']));
            $r = true;
        }

        return $r;
    }
}}
?>