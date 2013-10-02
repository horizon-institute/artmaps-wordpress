<?php
$objectID = 75258;
$lookup_object_page = new WP_Query('post_type=artwork&meta_key=object_id&meta_value=' . $objectID);
        if ( $lookup_object_page->have_posts() ) {
      	while ( $lookup_object_page->have_posts() ) {
      		$lookup_object_page->the_post();
      		$pageID = get_the_ID();
      	} } else {$pageID = "no";}
      	
      	echo $pageID;
      	
      	?>