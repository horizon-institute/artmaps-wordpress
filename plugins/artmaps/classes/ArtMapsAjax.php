<?php
if(!class_exists('ArtMapsAjax')) {
class ArtMapsAjax {

    private $rpc;

    public function __construct() {
        require_once('ArtMapsRpc.php');
        $this->rpc = new ArtMapsRpc();
    }

    public function generateCommentTemplate($objectID) {
        try {
            return json_encode($this->rpc->generateCommentTemplate($objectID));
        }
        catch(ArtMapsCoreServerException $e) {
            return '{"ErrorCode":"' . $e->getCode() . '",'
                    . '"ErrorMessage":"' . $e->getMessage() . '"}';
        }
    }

    public function createDraftComment($objectID) {
        require_once('ArtMapsUser.php');
        $user = ArtMapsUser::currentUser();
        $blog = $user->getExternalBlog();
        $tmpl = $this->rpc->generateCommentTemplate($objectID);

        require(ABSPATH . '/wp-includes/class-IXR.php');
        $client = new IXR_Client($blog->url . '/xmlrpc.php');
        if(!$client->query('blogger.newPost',
                array(
                        '',
                        '',
                        $blog->username,
                        $blog->password,
                        $tmpl,
                        false)))
            return '{"ErrorCode":"' . $client->getErrorCode() . '",'
                    . '"ErrorMessage":"' . $client->getErrorMessage() . '"}';

        return '{"BlogUrl":"'
                . $blog->url . '/wp-admin/post.php?action=edit&post='
                . $client->getResponse()
                . '"}';
    }

    public function publishComment($objectID, $text) {
        require_once('ArtMapsUser.php');
        $user = ArtMapsUser::currentUser();
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        require_once('ArtMapsBlog.php');
        $blog = $nw->getCurrentBlog();

        $postID = $blog->getPageForObject($objectID);

        global $wpdb;
        wp_insert_comment(array(
                        'comment_post_ID' => (int)$postID,
                        'comment_author' => $wpdb->escape($user->getLogin()),
                        'comment_content' => $wpdb->escape($text),
                        'comment_type' => 'comment',
                        'user_id' => (int)$user->getID()
                ));

        return "{}";
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
}}
?>
