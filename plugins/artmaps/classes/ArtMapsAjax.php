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

    public function deleteComment($commentID) {
        require_once('ArtMapsUser.php');
        $user = ArtMapsUser::currentUser();
        $comment = get_comment($commentID);
        if($comment == null)
            return json_encode(false);
        if($comment->user_id != $user->getID())
            return json_encode(false);
        wp_delete_comment($commentID);
        return json_encode(true);
    }

    public function hasComments($objectID) {
        require_once('ArtMapsNetwork.php');
        $n = new ArtMapsNetwork();
        $b = $n->getCurrentBlog();
        $pageID = $b->getPageIDForObject($objectID);
        if($pageID == null)
            return json_encode(false);
        $comments = get_comments(array('post_id' => $pageID, 'status' => 'approve'));
        return json_encode(count($comments) != 0);
    }
}}
?>
