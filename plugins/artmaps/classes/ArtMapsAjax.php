<?php
if(!class_exists('ArtMapsAjax')) {
class ArtMapsAjax {

    private $rpc;

    public function __construct() {
        require_once('ArtMapsRpc.php');
        $this->rpc = new ArtMapsRpc();
    }

    public function signData($data) {
        require_once('ArtMapsNetwork.php');
        $n = new ArtMapsNetwork();
        $b = $n->getCurrentBlog();
        require_once('ArtMapsCrypto.php');
        $c = new ArtMapsCrypto();
        require_once('ArtMapsUser.php');
        $signed = $c->signData(
                $data,
                $b->getKey(),
                ArtMapsUser::currentUser());
        return json_encode($signed);
    }

    public function storeMapState($state) {
        session_start();
        $_SESSION['mapState'] = $state;
        return json_encode(true);
    }
}}
?>
