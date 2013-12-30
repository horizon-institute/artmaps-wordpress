<div id="overlay"></div>
<div></div>
<?php wp_footer(); ?>
<?php if(function_exists("oa_social_login_add_javascripts")) { ?>
  <?php oa_social_login_request_email(); ?>
<?php } ?>
</body>
</html>