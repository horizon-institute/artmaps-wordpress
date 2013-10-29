<?php
if(!class_exists('ArtMapsDigest')) {
class ArtMapsDigest {

    public function sendDigestEmail() {
        require_once('ArtMapsDigest.php');
        require_once('ArtMapsNetwork.php');
        require_once('ArtMapsCoreServer.php');
        require_once('ArtMapsTemplating.php');
        $nw = new ArtMapsNetwork();
        $blog = $nw->getCurrentBlog();
        $cs = new ArtMapsCoreServer($blog);
        $te = new ArtMapsTemplating();

        $objects = $this->fetchNonFinalised();
        if(count($objects) == 0)
            return;
        foreach($objects as $object) {
            $object->metadata = $cs->fetchObjectMetadata($object->ID);
        }

        $headers = array(
                'Content-Type: text/html',
                'From: ArtMaps <' . get_option('admin_email') . '>'
         );
        $body = $te->renderDigestEmail($objects);

        $admins = get_users(array( 'role' => 'administrator'));
        foreach($admins as $admin) {
            wp_mail($admin->user_email, '[' . get_bloginfo('name')
                    . '] Daily Digest', $body, $headers);
        }
    }

    public function fetchNonFinalised() {
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        $blog = $nw->getCurrentBlog();
        require_once('ArtMapsCoreServer.php');
        $cs = new ArtMapsCoreServer($blog);
        $objects = $cs->searchByLocation(9000000000, -9000000000, 18000000000, -18000000000);
        // Filter to objects with more than one assigned location
        $objects = array_filter($objects, function($object) {
            return count($object->locations) > 1;
        });
        if(count($objects) == 0)
            return $objects;
        // Filter to objects with no final location
        $objects = array_filter($objects, function($object) {
            $finalisations = array_filter($object->actions, function($action) {
                return strpos($action->URI, 'finalisation') !== false;
            });
            return count($finalisations) == 0;
        });
        if(count($objects) == 0)
            return $objects;
        // Filter to objects with 5 or more suggestions (taking into account deletions)
        $objects = array_filter($objects, function($object) {
            $deletions = array_filter($object->actions, function($action) {
                return strpos($action->URI, 'deletion') !== false;
            });
            $deletedLocations = array();
            foreach($deletions as $deletion) {
                $o = json_decode(str_replace('deletion://', '', $deletion->URI));
                $deletedLocations[] = $o->LocationID;
            }
            $suggestions = array_filter($object->actions, function($action) {
                return strpos($action->URI, 'suggestion') !== false;
            });

                $active = array();
                foreach($suggestions as $suggestion) {
                    $o = json_decode(str_replace('suggestion://', '', $suggestion->URI));
                    if(array_search($o->LocationID, $deletedLocations) === false)
                        $active[] = $suggestion;
                }

                return count($active) >= 5;
        });
        if(count($objects) == 0)
            return $objects;
        return $objects;
    }

}}
?>