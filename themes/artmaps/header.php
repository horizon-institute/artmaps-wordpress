<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
      <meta charset="<?php bloginfo( 'charset' ); ?>" />
      <meta name="viewport" content="user-scalable=0, initial-scale=1, maximum-scale=1, minimum-scale=1" />
      <title><?php bloginfo('name'); ?> &middot; <?php is_front_page() ? bloginfo('description') : wp_title(''); ?></title>
      <meta name="description" content="<?php bloginfo( 'description' ); ?>">
      <?php wp_head(); ?>
  </head>

  <body <?php body_class(); ?>>
  <header>
  <a href="<?php bloginfo('url'); ?>" class="logo">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/artmaps-logo.png" alt="<?php bloginfo('title'); ?>" />
  </a>
  <ul class="menu">
    <li></li>
    <?php if ( !is_user_logged_in() ) { ?>
    <li class="log-in"><a href="<?php echo wp_login_url( get_bloginfo('url') ); ?>" data-fancybox-href="<?php echo wp_login_url( get_bloginfo('url') ); ?>" class="fancybox fancybox.ajax">Log in</a></li>
    <?php } else { ?>
    <li class="log-out">
      <?php
        global $current_user;
        get_currentuserinfo();
        echo get_avatar( $current_user->ID, 64 );
      ?>
      <span class="logged-in-username"><?php echo $current_user->display_name; ?></span>
      <a href="<?php echo wp_logout_url( get_bloginfo('url') ); ?>" data-fancybox-href="<?php echo wp_logout_url( get_bloginfo('url') ); ?>">Log out</a>
    </li>
    <?php } ?>
  </ul>
  <input id="artmaps-map-autocomplete" type="text" placeholder="Find a place" />
  <a data-fancybox-href="<?= get_stylesheet_directory_uri(); ?>/search.html" class="fancybox fancybox.ajax">Search for Artworks</a>
  </header>
  <span class="loading-indicator gmnoprint">Searching this area for art&hellip;</span>