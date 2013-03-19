<?php
if(!class_exists('ArtMapsNetworkAdmin')) {
class ArtMapsNetworkAdmin {

    public function register() {
        add_submenu_page(
                'settings.php',
                'ArtMaps Settings',
                'ArtMaps Settings',
                'manage_sites',
                'artmaps-admin-page',
                array($this, 'display'));
    }

    public function display() {
        if(!current_user_can('manage_network_options'))
            wp_die(__('You do not have sufficient permissions to access this page.'));
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        $updated = $this->checkSubmission($nw);
        require_once('ArtMapsTemplating.php');
        $tpl = new ArtMapsTemplating();
        echo $tpl->renderNetworkAdminPage(
                $updated,
                $nw->getCoreServerUrl(),
                $nw->getMasterKey() != '',
                $nw->getGoogleMapsKey(),
                $nw->getIpInfoDbApiKey());
    }

    private function checkSubmission(ArtMapsNetwork $nw) {
        $r = false;

        if(isset($_POST['artmaps_core_server_url'])) {
            $nw->setCoreServerUrl($_POST['artmaps_core_server_url']);
            $r = true;
        }

        if(isset($_POST['artmaps_master_key'])
                && $_POST['artmaps_master_key'] != '') {
            $nw->setMasterKey($_POST['artmaps_master_key']);
            $r = true;
        }

        if(isset($_POST['artmaps_google_maps_api_key'])
                && $_POST['artmaps_google_maps_api_key'] != '') {
            $nw->setGoogleMapsKey($_POST['artmaps_google_maps_api_key']);
            $r = true;
        }

        if(isset($_POST['artmaps_ipinfodb_api_key'])
                && $_POST['artmaps_ipinfodb_api_key'] != '') {
            $nw->setIpInfoDbApiKey($_POST['artmaps_ipinfodb_api_key']);
            $r = true;
        }

        return $r;
    }
}}
?>