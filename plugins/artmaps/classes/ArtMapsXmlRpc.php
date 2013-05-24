<?php
if(!class_exists('ArtMapsXmlRpc')) {
class ArtMapsXmlRpc {

    const ERROR_CORE_SERVER_COMM_FAILURE = 10;

    private $rpc;

    public function __construct() {
        require_once('ArtMapsRpc.php');
        $this->rpc = new ArtMapsRpc();
    }

    public function generateCommentTemplate($objectID) {
        try {
            return $this->rpc->generateCommentTemplate($objectID);
        }
        catch(ArtMapsCoreServerException $e) {
            return new IXR_Error(
                    ArtMapsXmlRpc::ERROR_CORE_SERVER_COMM_FAILURE,
                    $e->getMessage());
        }
    }

    function doPingback($args) {
        global $wp_xmlrpc_server;

        if(count($args) != 2)
            return $wp_xmlrpc_server->pingback_ping($args);

        $remoteUrl = str_replace('&amp;', '&', $args[0]);
        $localUrl = $args[1];

        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        require_once('ArtMapsBlog.php');
        $blog = $nw->getCurrentBlog();

        $matches = array();
        $objectID = '';
        $postID = '';
    	$site = home_url();
    	$site = str_replace('http://', '', $site);
    	$site = str_replace('https://', '', $site);
    	$site = str_replace('/', '\\/', $site);
    	$site = str_replace('.', '\\.', $site);
        if(preg_match('/^https?:\\/\\/' . $site . '\\/object\\/(\\d+)\\/?.*$/', $localUrl, $matches)) {
            $objectID = $matches[1];
            $postID = $blog->getPageForObject($objectID);
            if($postID == null || $postID == '')
                return $wp_xmlrpc_server->pingback_ping($args);
        }
        else {
            $postID = url_to_postid($localUrl);
            $objectID = $blog->getObjectForPage($postID);
            if($objectID == null || $objectID == '')
                return $wp_xmlrpc_server->pingback_ping($args);
        }

        $post = get_post($postID);
        if (!$post)
            return $wp_xmlrpc_server->pingback_ping($args);

        global $wpdb;
        if($wpdb->get_results(
                $wpdb->prepare(
                        "SELECT * FROM $wpdb->comments "
                        . "WHERE comment_post_ID = %d "
                        . "AND comment_author_url = %s", $postID, $remoteUrl) ) )
            return new IXR_Error(48, __('The pingback has already been registered.'));

        $doc = new DOMDocument();
        @$doc->loadHTML(wp_remote_fopen($remoteUrl));
        $xpath = new DOMXPath($doc);

        $defaultAuthor = 'Anonymous';
        $author = $defaultAuthor;
        try {
            $author = $xpath->query("//title")->item(0)->textContent;
            if($author == null || $author == '')
                $author = $defaultAuthor;
        }
        catch(Exception $e) {
            $author = $defaultAuthor;
        }

        $mbody = $xpath->query('//div[@id=\'artmaps-post-body\']');
        $comment = '';
        if($mbody->length > 0) {
            try {
                $body = $xpath->query('//div[@id=\'artmaps-post-body\']')->item(0);
                $body->removeChild($xpath->query('//div[@id=\'artmaps-data-section\']')->item(0));
                $images = $xpath->query('//div[@id=\'artmaps-post-body\']//img', $body);
                $lines = explode('\n', trim($body->textContent));
                $comment = "<div class=\"artmaps-comment-text\">$lines[0]</div>";
                if($images->length > 0)
                    $comment = '<div class="artmaps-comment-image">'
                        . $images->item(0)->C14N() . '</div>' . $comment;
            }
            catch(Exception $e) {
                $comment = '';
            }
        }

        wp_insert_comment(array(
                'comment_post_ID' => (int)$postID,
                'comment_author' => $wpdb->escape($author),
                'comment_author_url' => $wpdb->escape($remoteUrl),
                'comment_author_email' => '',
                'comment_content' => $wpdb->escape($comment),
                'comment_type' => 'pingback'
        ));

        return "Pingback from $remoteUrl to $localUrl registered.";
    }

    public function fetchComments($objectID) {
        $network = new ArtMapsNetwork();
        $blog = $network->getCurrentBlog();
        $pageID = $blog->getPageForObject($objectID);
        $r = array();
        foreach(get_approved_comments($pageID) as $comment) {
            $c = array(
                    'title' => $comment->comment_author,
                    'text' => $comment->comment_content,
                    'url' => $comment->comment_author_url
            );
            array_push($r, $c);
        }
        return $r;
    }
}}
?>
