<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
      <meta charset="<?php bloginfo( 'charset' ); ?>" />
      <meta name="viewport" content="user-scalable=0, initial-scale=1" />
      <title><?php wp_title(); ?></title>
      <meta name="description" content="<?php bloginfo( 'description' ); ?>">
      <?php wp_head(); ?>
  </head>

  <body <?php body_class(); ?>>
  <div class="headertest"></div>
  <header>
  <a href="<?php bloginfo('url'); ?>" class="logo">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/tate-logo.png" alt="<?php bloginfo('title'); ?>" />
  </a>
  <ul class="menu">
    <li></li>
    <?php if ( !is_user_logged_in() ) { ?>
    <li><a href="<?php echo wp_login_url( get_bloginfo('url') ); ?>" data-fancybox-href="<?php echo wp_login_url( get_bloginfo('url') ); ?>" class="fancybox fancybox.ajax">Log in</a></li>
    <?php } else { ?>
    <li><a href="<?php echo wp_logout_url( get_bloginfo('url') ); ?>" data-fancybox-href="<?php echo wp_logout_url( get_bloginfo('url') ); ?>">Log out</a></li>
    <?php } ?>
    <li><a href="<?php bloginfo('url'); ?>/about" data-fancybox-href="<?php bloginfo('url'); ?>/about" class="fancybox fancybox.ajax">About</a></li>
    <li><a href="<?= get_stylesheet_directory_uri(); ?>/search.html" data-fancybox-href="<?= get_stylesheet_directory_uri(); ?>/search.html" class="fancybox fancybox.ajax">Search for Artworks</a></li>
  </ul>
  <input id="artmaps-map-autocomplete" type="text" />
  </header>
