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
    <img src="<?php header_image(); ?>" height="<?php echo get_custom_header()->height; ?>" width="<?php echo get_custom_header()->width; ?>" alt="<?php bloginfo('title'); ?>" />
  </a>
  <ul class="menu">
    <li><a href="">Log in</a></li>
    <li><a href="">About</a></li>
  </ul>
  <input id="artmaps-map-autocomplete" type="text" />
  </header>