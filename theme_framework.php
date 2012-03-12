<?php
/*
Plugin Name: Theme Framework
Description: Adds a bunch of useful tools and functions for any Wordpress theme
Version: 1.1
Author: Adam Turtle
Author URI: http://adamturtle.com
*/



/**
	* Theme settings
	* Choose theme settings here
	*/
	
	$theme_settings = array(
		'disable_wordpress_upgrade_notices'	=> false,
		'show_theme_options_page'						=> false
	);
	

	
	/* Don't edit anything beyond here. --------------------------------------------*/

	$plugin_directory = plugins_url('',__FILE__); $plugin_path = dirname(__FILE__).'';
	
	// Theme options page
	if( $theme_settings['show_theme_options_page'] != false ){
		include_once($plugin_path . '/theme-options-page.php');	
	}
	
	// Disable WP upgrade notices
	if( $theme_settings['disable_wordpress_upgrade_notices'] != false ){
		add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );		
	}
	
	// Load theme functions
	include_once($plugin_path . '/class.themeframework.php');