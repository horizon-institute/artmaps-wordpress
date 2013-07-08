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

    public function createUser($username, $password, $email, $displayName, $blog) {
        require_once('ArtMapsUser.php');
        try {
            $user = ArtMapsUser::create($username, $password, $email);
            $user->setBlogUrl($blog);
            $user->setDisplayName($displayName);
            return json_encode(true);
        } catch(ArtMapsUserCreationException $e) {
            return json_encode($e->getMessage());
        }
    }
}}
?>
