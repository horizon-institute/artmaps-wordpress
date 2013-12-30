<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
      <meta charset="<?php bloginfo( 'charset' ); ?>" />
      <meta name="viewport" content="user-scalable=0, initial-scale=1, maximum-scale=1, minimum-scale=1" />
      <title><?php bloginfo('name'); ?> &middot; <?php is_front_page() ? bloginfo('description') : wp_title(''); ?></title>
      <meta name="description" content="<?php bloginfo( 'description' ); ?>">
      <?php wp_head(); ?>
      <?php if(function_exists("oa_social_login_add_javascripts")) { oa_social_login_add_javascripts(); } ?>
  </head>

  <body <?php body_class(); ?>>
  <header>
  <a href="<?php bloginfo('url'); ?>" class="logo">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/artmaps-logo.png" alt="<?php bloginfo('title'); ?>" />
  </a>
  <ul class="menu">
    <li class="about">
      <a href="about" class="toggle fancybox fancybox.ajax" id="how-it-works">How to play</a>
      <a href="about" class="toggle fancybox fancybox.ajax" id="how-it-works">What's new?</a>
    </li>
    <li class="mylocation" onclick="window.main_map.centerOnMyLocation()" style="display: none;">
        <a class="toggle">My Location</a>
    </li>

    <li class="settings">
      <div id="map-settings">
        <a class="toggle">Settings</a>
        <div class="settings popover">
          <div class="settings-inner">

          </div>
        </div>
      </div>
    </li>
    <?php if ( !is_user_logged_in() ) { ?>
    <li class="log-in">
      <a href="<?php echo wp_login_url( get_bloginfo('url') ); ?>" id="log-in" class="toggle">Log in</a>
      <div class="log-in-popover popover" id="log-in-popover">
        <p class="status"></p>
        <?php wp_login_form(array('label_username' => 'Email address', 'label_log_in' => 'Log in', 'label_remember' => 'Remember me')); ?>
        <?php if(function_exists("oa_social_login_add_javascripts")) { ?>
        <div style="background-color:#fff; height:100px; text-align:center; overflow:hidden;">
          <?php do_action('login_form'); ?>
        </div>
        <?php } ?>
      </div>
    </li>
    <?php } else { ?>
    <li class="log-out">
      <?php
        global $current_user;
        get_currentuserinfo();
      ?>
      <a href="#" id="log-in" class="toggle"><?php echo get_avatar( $current_user->ID, 32 ); ?></a>
      <div class="log-out-popover popover" id="log-in-popover">
        <div class="wrap">
          <?php echo get_avatar( $current_user->ID, 64 ); ?>
          <?php $current_user = wp_get_current_user(); ?>
          <ul>
            <li><?php echo $current_user->display_name; ?></li>
            <li><a href="<?php echo wp_logout_url( get_bloginfo('url') ); ?>">Log out</a></li>
          </ul>
        </div>
      </div>
    </li>
    <?php } ?>
  </ul>
  </header>
  <div class="search-form">
    <a class="switch" href="#" id="search-form-toggle"><i class="fa-search"></i><span id="search-label-places">Places</span><span id="search-label-art">Art</span><i class="fa-chevron-down"></i></a>
    <div id="location-search-form">
      <input id="artmaps-map-autocomplete" type="text" placeholder="Find a place" class="query-field" />
    </div>
    <div id="keyword-search-form">
      <form>
        <input placeholder="Search by keyword" name="term" value="" type="text" size="30" autocomplete="off" class="query-field">
        <input type="submit" style="display:none;">
      </form>
    </div>
  </div>
  <span class="loading-indicator gmnoprint">Searching this area for art&hellip;</span>

  <script type="text/javascript">
  jQuery(document).ready(function(){
      if(window.location) {
          jQuery(".mylocation").css("display", "inline");
      }
  });
  </script>
