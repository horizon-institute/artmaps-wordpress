<div id="activity-sidebar">

  <div style="background:#666; color:#fff; margin: -20px -20px 20px; padding: 20px;">
    <h2>Challenges</h2>
    <p>This week's challenge is to lorem ipsum lorem ipsum lorem ipsum.</p>  
  </div>
  
  <h2>Recent activity</h2>
  <ul class="commentlist">
  <?php
  $args = array(
  	'number' => '5'
  );
  $comments = get_comments($args);
  
  foreach($comments as $comment) { ?>
    <li id="comment-13" class="comment byuser comment-author-admin bypostauthor even thread-even depth-1">
			<article id="div-comment-13" class="comment-body">
				<footer class="comment-meta">
					<div class="comment-author vcard">
						<img alt="" src="http://1.gravatar.com/avatar/9270438096ff635a48186cf12e67e141?s=32&amp;d=http%3A%2F%2F1.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&amp;r=G" class="avatar avatar-32 photo" height="32" width="32">						<b class="fn">Bart Simpson</b> <span class="says">says:</span>					</div><!-- .comment-author -->

					<div class="comment-metadata">
						<a href="http://artmaps.local/tate/object/78947/#comment-13">
							commented on <?php echo get_the_title($comment->comment_post_ID); ?> <time datetime="2013-12-30T17:13:11+00:00" title="12/30/2013 5:13:11 PM">
								<?php echo date(get_option('time_format'),$comment->comment_date); ?>
						</time>
						</a>
											</div><!-- .comment-metadata -->

									</footer><!-- .comment-meta -->

				<div class="comment-content">
  				<?php if(get_post_meta($comment->comment_post_ID,"imageurl",true)) { ?>
            <a href="<?php echo get_post_meta($comment->comment_post_ID,"imageurl",true); ?>" class="fancybox"><img src="http://dev.artmaps.org.uk/artmaps/tate/dynimage/x/60/<?php echo get_post_meta($comment->comment_post_ID,"imageurl",true); ?>" /></a>
          <?php } else { ?>
            <img src="{'/content/unavailable.jpg'|artmapsUri}" alt="{$metadata->title}" />
          <?php } ?>
					<p><?php echo $comment->comment_content; ?></p>
				</div>

			</article><!-- .comment-body -->
    </li>
    
  <?php } ?>
</div>

<div id="overlay"></div>

<?php wp_footer(); ?>
<?php if(function_exists("oa_social_login_add_javascripts")) { ?>
  <?php oa_social_login_request_email(); ?>
<?php } ?>
</body>
</html>