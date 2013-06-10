<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title><?php wp_title(); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div id="artmaps-nav-bar">
    	<div id="artmaps-nav-bar-home" class="artmaps-nav-bar-link">
    	    <a href="<?= site_url() ?>/?p=1">Home</a>
    	</div>
    	<div id="artmaps-nav-bar-map" class="artmaps-nav-bar-link">
    	    <a href="<?= site_url('/map') ?>">The Art Map</a>
    	</div>
    	<div id="artmaps-nav-bar-<?= is_user_logged_in() ? 'logout' : 'login' ?>" class="artmaps-nav-bar-link">
    	    <?php wp_loginout() ?>
    	</div>
    </div>
