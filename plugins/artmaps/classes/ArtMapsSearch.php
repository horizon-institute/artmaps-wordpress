<?php
if(!class_exists('ArtMapsSearch')) {
class ArtMapsSearch {

    public function preSearch($query) {
        if(!$query->is_search())
            return;
        if(!$query->get('paged'))
            $query->set('paged', 1);
    }

    public function search($posts) {
        global $wp_query;
        if(!is_search() || isset($wp_query->artmaps_search_complete))
            return $posts;
        $wp_query->artmaps_search_complete = true;
        $term = $wp_query->query_vars['s'];
        require_once('ArtMapsNetwork.php');
        require_once('ArtMapsBlog.php');
        require_once('ArtMapsCoreServer.php');
        $nw = new ArtMapsNetwork();
        $blog = $nw->getCurrentBlog();
        $core = new ArtMapsCoreServer($blog);
        $page = intval($wp_query->get('paged')) - 1;
        $results = $core->search($term, $page);
        $posts = array();
        foreach($results as $result) {
            $pageID = $blog->getPageForObject($result->ID);
            array_push($posts, get_page($pageID));
        }
        $wp_query->max_num_pages = 999;
        return $posts;
    }
}}
?>
