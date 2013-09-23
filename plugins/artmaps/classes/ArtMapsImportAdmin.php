<?php
if(!class_exists('ArtMapsImportAdmin')) {
class ArtMapsImportAdmin {

    public function register() {
        add_submenu_page(
                'tools.php',
                'ArtMaps Import',
                'ArtMaps Import',
                'manage_options',
                'artmaps-import-admin-page',
                array($this, 'display'));
    }

    public function display() {
        if(!current_user_can('manage_options'))
            wp_die(__('You do not have sufficient permissions to access this page.'));
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        $blog = $nw->getCurrentBlog();
        $imported = $this->checkSubmission($blog);
        require_once('ArtMapsTemplating.php');
        $tpl = new ArtMapsTemplating();
        require_once('ArtMapsImport.php');
        $all = ArtMapsImport::all($blog);
        echo $tpl->renderImportAdminPage($imported, $all);
    }

    private function checkSubmission(ArtMapsBlog $blog) {
        $r = false;

        $name = isset($_POST['artmaps_import_label']) ? $_POST['artmaps_import_label'] : 'Unnamed';

        if(isset($_FILES['artmaps_import_file'])) {
        	$file = $_FILES['artmaps_import_file']['tmp_name'];
        	require_once('ArtMapsImport.php');
        	$import = ArtMapsImport::createNew($blog, $file, $name);
            $r = true;
        }

        return $r;
    }
}}
?>