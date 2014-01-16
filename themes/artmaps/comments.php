<script type="text/javascript">
// AJAXified commenting system
jQuery('document').ready(function($){
var commentform=$('#commentform'); // find the comment form
commentform.before('<div id="comment-status" ></div>'); // add info panel before the form to provide feedback or errors
var statusdiv=$('#comment-status'); // define the infopanel

commentform.submit(function(){
//serialize and store form data in a variable
var formdata=commentform.serialize();
//Add a status message
statusdiv.html('<p>Processing...</p>');
//Extract action URL from commentform
var formurl=commentform.attr('action');
//Post Form with data
$.ajax({
type: 'post',
url: formurl,
data: formdata,
error: function(XMLHttpRequest, textStatus, errorThrown){
statusdiv.html('<p class="wdpajax-error" >You might have left one of the fields blank, or be posting too quickly.</p>');
},
success: function(data, textStatus){
if(data=="success") {
  statusdiv.html('<p class="ajax-success" >Thanks for your comment. We appreciate your response.</p>');
  commentform.hide();
} else {
  statusdiv.html('<p class="ajax-error" >Please wait a while before posting your next comment.</p>');
  commentform.find('textarea[name=comment]').val('');
}
}
});
return false;

});
});</script>
<h2>Discuss this artwork's location</h2>
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
  if(is_user_logged_in()) {
    $current_avatar = get_avatar( $current_user->ID, 35 ); 
  } else {
    $current_avatar = '';
  }
  $args = array('title_reply' => $current_avatar,  'logged_in_as' => '', 'comment_notes_before' => '', 'comment_notes_after' => '', 'must_log_in' => '<p class="not-logged-in">Please <a href="#" class="log-in-trigger">log in</a> to post a comment.</p>');
  comment_form($args);
?>