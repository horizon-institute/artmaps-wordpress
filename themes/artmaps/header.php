<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
      <meta charset="<?php bloginfo( 'charset' ); ?>" />
      <title><?php bloginfo('name'); ?> &middot; <?php is_front_page() ? bloginfo('description') : wp_title(''); ?></title>
      <meta name="description" content="<?php bloginfo( 'description' ); ?>">
      
      <meta name="viewport" content="user-scalable=0, initial-scale=1, maximum-scale=1, minimum-scale=1, minimal-ui" />
      <link rel="apple-touch-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/apple-touch-icon.png">
      <link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" type="image/x-icon">
      <meta http-equiv="x-ua-compatible" content="ie=edge">
      <meta name="apple-mobile-web-app-capable" content="yes">
      
      <?php wp_head(); ?>
      <?php if(function_exists("oa_social_login_add_javascripts")) { oa_social_login_add_javascripts(); } ?>
  </head>

  <body <?php body_class(); ?>>
  <header>
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
            <p class="intro">To suggest and discuss locations, please log in. You can use an existing account from the services below, or create an account now with your email address.</p>
            <?php echo do_shortcode("[lwa template=divs-only]"); ?>
          </div>
        </li>
        <?php } else { ?>
        <li class="log-out">
          <?php
            global $current_user;
            get_currentuserinfo();
          ?>
          <a href="#" id="log-in" class="toggle">
            <span><?php echo $current_user->display_name[0]; ?>
              <?php echo get_avatar( $current_user->ID, 32, get_stylesheet_directory_uri()."/images/no-avatar.png" ); ?>
            </span>
          </a>
          <div class="log-out-popover popover" id="log-in-popover">
            <div class="wrap">
              <?php echo get_avatar( $current_user->ID, 64) ?>
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
    </nav>
  </header>
  <div class="search-form">
    <span class="switch" id="search-form-toggle">
      <select id="search-mode">
        <optgroup label="Search by">
          <option id="search-mode-places">Place</option>
          <option id="search-mode-artworks">Art</option>
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
  </div>
  <span class="loading-indicator gmnoprint">Searching this area for art&hellip;</span>

  <script type="text/javascript">
  jQuery(document).ready(function(){
      if(window.location) {
          jQuery(".mylocation").css("display", "inline");
      }
  });
  </script>
