<div id="about-sidebar"></div>
<div id="activity-sidebar">

  <div class="challenges">
    <h1>Challenges</h1>
    <p>Not sure where to start? Try helping with one of our regular community challenges.</p>
  </div>
  <h1>Recent activity</h1>
  <ul class="commentlist">
  <?php
  $args = array(
  	'number' => '20'
  );
  $comments = get_comments($args);
  
  foreach($comments as $comment) { ?>
    <li <?php comment_class('', $comment->comment_ID); ?>>
			<article class="comment-body">
				
  				<?php if(get_post_meta($comment->comment_post_ID,"imageurl",true)) { ?>
            <a href="<?php echo get_permalink($comment->comment_post_ID); ?>#comment-<?php echo $comment->comment_ID; ?>" class="artwork-link" data-object-id="<?php echo get_post_meta($comment->comment_post_ID,"object_id",true); ?>">
              <img src="http://dev.artmaps.org.uk/artmaps/tate/dynimage/x/65/<?php echo get_post_meta($comment->comment_post_ID,"imageurl",true); ?>" />
              <h2 class="artmaps-map-object-container-title">
                <?php if(get_post_meta($comment->comment_post_ID,"title",true)) { echo get_post_meta($comment->comment_post_ID,"title",true);} else { echo get_the_title($comment->comment_post_ID); } ?>
              </h2>
              <em>by <span class="artmaps-map-object-container-artist"><?php echo get_post_meta($comment->comment_post_ID,"artist",true); ?></span></em>
            </a>
          <?php } else { ?>
            
          <?php } ?>
        <footer class="comment-meta">
					<div class="comment-author vcard">
						<?php echo get_avatar($comment->comment_author_email, "32") ?>
						<b class="fn"><?php echo $comment->comment_author; ?></b>
					</div>

					<div class="comment-metadata">
						<a href="<?php echo get_permalink($comment->comment_post_ID); ?>#comment-<?php echo $comment->comment_ID; ?>" data-object-id="<?php echo get_post_meta($comment->comment_post_ID,"object_id",true); ?>">
							commented <time datetime="<?php echo date("c",strtotime($comment->comment_date)); ?>" title="12/30/2013 5:13:11 PM">
								<?php echo $comment->comment_date; ?>
						</time>
						</a>
					</div>

				</footer>
        <div class="comment-content">
					<p><?php echo $comment->comment_content; ?></p>
				</div>

			</article><!-- .comment-body -->
    </li>
    
  <?php } ?>
  </ul>
</div>
<div id="overlay"></div>

<?php wp_footer(); ?>
<?php if(function_exists("oa_social_login_add_javascripts")) { ?>
  <?php oa_social_login_request_email(); ?>
<?php } ?>
</body>
</html>