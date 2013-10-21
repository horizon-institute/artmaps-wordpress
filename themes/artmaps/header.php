<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
      <meta charset="<?php bloginfo( 'charset' ); ?>" />
      <title><?php wp_title(); ?></title>
      <meta name="description" content="<?php bloginfo( 'description' ); ?>">
      <!--<link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>" />-->
      <?php wp_head(); ?>
  </head>
  
  <body <?php body_class(); ?>>
  <div class="headertest"></div>
  <header>
  <a href="<?php bloginfo('url'); ?>" class="logo">
    <!--<span style="font-weight:bold; font-size:24px; color:#fff;">Art Maps</span>-->
    <img src="<?php header_image(); ?>" height="<?php echo get_custom_header()->height; ?>" width="<?php echo get_custom_header()->width; ?>" alt="<?php bloginfo('title'); ?>" />
  </a>
  <ul class="menu">
    <li><a href="<?php bloginfo('url'); ?>/login" data-fancybox-href="<?php bloginfo('url'); ?>/login" class="fancybox fancybox.ajax">Log in</a></li>
    <li><a href="<?php bloginfo('url'); ?>/about" data-fancybox-href="<?php bloginfo('url'); ?>/about" class="fancybox fancybox.ajax">About</a></li>
  </ul>
  <input id="artmaps-map-autocomplete" type="text" />
  </header>