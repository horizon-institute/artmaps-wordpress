<ul class="comment-list">
  <?php 
      $args = array(
    	'avatar_size'       => 35
    );
    wp_list_comments($args);
  ?>
</ul>
<?php
  global $current_user;
  get_currentuserinfo();
  $current_avatar = get_avatar( $current_user->ID, 35 ); 
  $args = array('title_reply' => $current_avatar,  'logged_in_as' => '', 'comment_notes_before' => '', 'comment_notes_after' => '');
  comment_form($args);
?>