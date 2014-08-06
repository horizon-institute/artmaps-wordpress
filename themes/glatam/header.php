<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
      <meta charset="<?php bloginfo( 'charset' ); ?>" />
      <title><?php bloginfo('name'); ?></title>
      <meta name="description" content="<?php bloginfo( 'description' ); ?>">
      
      <meta name="viewport" content="user-scalable=0, initial-scale=1, maximum-scale=1, minimum-scale=1, minimal-ui" />
      <link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" type="image/x-icon">
      <meta http-equiv="x-ua-compatible" content="ie=edge">
      
      <meta name="apple-mobile-web-app-capable" content="yes">
      <meta name="apple-mobile-web-app-title" content="<?php bloginfo('name'); ?>">
      <link rel="apple-touch-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/apple-touch-icon.png">
      <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
      
      <?php wp_head(); ?>
      <?php if(function_exists("oa_social_login_add_javascripts")) { oa_social_login_add_javascripts(); } ?>
  </head>

  <body class="<?php if(is_user_logged_in()==true) { ?>logged-in<?php } else { ?>logged-out<?php } ?>">
  <header>
    <a href="#" id="menu-toggle">Menu</a>
    <a href="#" id="search-toggle">Search</a>
    <nav>
      <a href="#" id="home" class="logo">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/artmaps-logo.png" alt="<?php bloginfo('title'); ?>" />
      </a>
      <ul class="menu">
        <li class="activity">
          <a href="#" class="toggle" id="whats-new">What's new?</a>
        </li>
        <li class="about">
          <a href="#" class="toggle" id="how-it-works">About</a>
        </li>
        <li class="settings">
          <div id="map-settings">
            <span class="toggle">
              <span class="menu-label">View mode</span>
              <div class="settings-inner"></div>
            </span>
          </div>
        </li>
        <li id="my-location">
          <a href="#" class="toggle"><span class="menu-label">My location</span></a>
        </li>
        <?php if ( !is_user_logged_in() ) { ?>
        <li class="log-in">
          <a href="<?php echo wp_login_url( get_bloginfo('url') ); ?>" id="account-button" class="toggle">Log in</a>
        </li>
        <?php } else { ?>
        <li class="log-out">
          <?php
            global $current_user;
            get_currentuserinfo();
          ?>
          <a href="#" id="account-button" class="toggle">
            <span><?php echo $current_user->display_name[0]; ?>
              <?php echo get_avatar( $current_user->ID, 32, get_stylesheet_directory_uri()."/images/no-avatar.png" ); ?>
            </span>
          </a>
        </li>
        <?php } ?>
      </ul>
    </nav>
  </header>
  <div class="search-form">
    <span class="switch" id="search-form-toggle">
      <select id="search-mode">
        <optgroup label="Search by">
          <option id="search-mode-places">Place</option>
          <option id="search-mode-artworks">Keyword</option>
        </optgroup>
      </select>
      <i class="fa-search"></i><span id="search-label-places">Places</span><span id="search-label-art">Art</span><i class="fa-chevron-down"></i>
    </span>
    <div id="location-search-form">
      <input id="artmaps-map-autocomplete" type="search" placeholder="Enter a location" autocorrect="off" autocapitalize="off" class="query-field" />
    </div>
    <div id="keyword-search-form">
      <form>
        <input type="search" placeholder="Search by keyword" name="term" autocorrect="off" autocapitalize="off" value="" size="30" autocomplete="off" class="query-field"><input type="submit">
      </form>
    </div>
    <span id="close-search">Close</span>
  </div>
  <span class="loading-indicator gmnoprint">Searching this area for art&hellip;</span>
  
  <div class="account-sidebar" id="account-sidebar">
  <?php if ( !is_user_logged_in() ) { ?>
    <div class="login-form">
      <p class="intro">To suggest and discuss locations, please log in. You can use an existing account from the services below, or create an account now with your email address.</p>
      <?php echo do_shortcode("[lwa template=artmaps]"); ?>
    </div>
  <?php } else { ?>
    <?php $current_user = wp_get_current_user(); ?>
    <?php echo get_avatar( $current_user->ID, 128) ?>
    <ul>
      <li><?php echo $current_user->display_name; ?></li>
      <li><a href="<?php echo wp_logout_url( get_bloginfo('url') ); ?>">Log out</a></li>
    </ul>
  <?php } ?>
  </div>
