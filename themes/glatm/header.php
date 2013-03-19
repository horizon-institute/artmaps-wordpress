<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title><?php
global $ArtmapsPageTitle;
if(isset($ArtmapsPageTitle))
    echo "$ArtmapsPageTitle | ";
else
    wp_title('|', true, 'right');
?><?php bloginfo('name');?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="artmaps-navigation-top" class="artmaps-navigation-container">
	<div id="artmaps-navigation-home" class="artmaps-navigation-link">
	    <a href="<?= get_home_url() ?>">Home</a></div>
	<div id="artmaps-navigation-map" class="artmaps-navigation-link">
	    <a href="<?= get_site_url() ?>/map">The Art Map</a></div>
	    <!--
	<div id="artmaps-navigation-loginout" class="artmaps-navigation-link">
	    <span class="<?= is_user_logged_in()
	        ? 'artmaps-navigation-logout'
	        : 'artmaps-navigation-login' ?>">
	    <?= wp_loginout($echo = false) ?>
	    </span>
	</div>
	 -->
</div>
