<?php
require_once('../../../wp-load.php');
$user = ArtMapsUser::fromID(1);
echo $user->getLogin() . '<br/>';

$feed = 'http://www.artmaps.org.uk/wordpress/gallery/feed/';
$rss = fetch_feed($feed);
$items = $rss->get_items(0);
foreach($items as $item) {
	echo $item->get_permalink() . '<br/>';
}
?>
