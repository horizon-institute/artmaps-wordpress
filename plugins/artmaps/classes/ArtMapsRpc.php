<?php
if(!class_exists('ArtMapsRpc')) {
class ArtMapsRpc {

    public function generateCommentTemplate($objectID) {
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        $blog = $nw->getCurrentBlog();
        require_once('ArtMapsCoreServer.php');
        $core = new ArtMapsCoreServer($blog);
        $metadata = $core->fetchObjectMetadata($objectID);
        $link = get_site_url() . '/object/' . $objectID;
        require_once('ArtMapsTemplating.php');
        $tmpl = new ArtMapsTemplating();
        return $tmpl->renderCommentTemplate($blog, $objectID, $link, $metadata);
    }
}}
?>