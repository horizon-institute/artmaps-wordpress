<?php
if(!class_exists('ArtMapsAjax')) {
class ArtMapsAjax {

    private $rpc;

    public function __construct() {
    	error_log("in function _construct");
        require_once('ArtMapsRpc.php');
        $this->rpc = new ArtMapsRpc();
    }

    public function createDraftComment($objectID) {
   		error_log("in function createDraftComment");
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
    	error_log("in function publishComment");
        require_once('ArtMapsUser.php');
        $user = ArtMapsUser::currentUser();
        $blog = $user->getExternalBlog();

        $tmpl = $this->rpc->generateCommentTemplate($objectID);

        $comment = "";

        try {
            $doc = new DOMDocument();
            $doc->preserveWhiteSpace = false;
            @$doc->loadHTML($tmpl);
            $xpath = new DOMXPath($doc);
            $con = $xpath->query('//div[@id=\'artmaps-comment-text\']')->item(0);
            $par = $con->parentNode;
            $text = $doc->createTextNode($text);
            $par->replaceChild($text, $con);
            $els = $xpath->query("//body/*");
            for($i = 0; $i < $els->length; $i++)
                $comment .= $els->item($i)->C14N();
        }
        catch(Exception $e) {
            $commment = "$tmpl<div id=\"artmaps-comment-text\">$text</div>";
        }

        require(ABSPATH . '/wp-includes/class-IXR.php');
        $client = new IXR_Client($blog->url . '/xmlrpc.php');
        if(!$client->query('blogger.newPost',
                array(
                        '',
                        '',
                        $blog->username,
                        $blog->password,
                        $comment,
                        true)))
            return '{"ErrorCode":"' . $client->getErrorCode() . '",'
            . '"ErrorMessage":"' . $client->getErrorMessage() . '"}';
        $postID = $client->getResponse();

        // The way WordPress processes pingbacks when a post is published
        // is to add the Pingbacks to a queue using <code>wp_schedule_single_event</code>.
        // The queue is not processed until a page on the site is called (typically
        // by a site visitor), in order to trigger the pingbacks immediately
        // we use curl to call the front page of the site.
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $blog->url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $r = curl_exec($c);
        curl_close($c);
        unset($c);

        return '{"BlogUrl":"'
        . $blog->url . '/wp-admin/post.php?action=edit&post='
        . $postID
        . '"}';
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
