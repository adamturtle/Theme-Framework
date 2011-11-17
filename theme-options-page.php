<?php

/**
 * Build theme options here. Documentation can be found at
 * http://codecanyon.markusthoemmes.de/adminpage/
 * 
 */

require_once($plugin_path . '/adminpage.class.php');

$options = new SubPage('theme', array(
	'menu_title' => 'Theme Setup',
	'page_title' => 'Knossos Setup',
	'menu_slug' => 'theme_setup',
));

$options->addTitle('Social Accounts');

	$options->addInput(array(
		'id' 		=> 'flickr_username', 
		'label' => 'Flickr Username',
		'size'	=> 'short'
	));

	$options->addInput(array(
		'id' 		=> 'facebook_id', 
		'label' => 'Facebook Group ID',
		'size'	=> 'short'
	));

	$options->addInput(array(
		'id' 		=> 'twitter_id', 
		'label' => 'Twitter Username',
		'size'	=> 'short'
	));

$options->addTitle('Gallery');

	$options->addDropdown(array(
		'id' 				=> 'gallery_image_count', 
		'standard'	=> 20, 
		'label' 		=> 'Gallery image count',
		'options' 	=> array(
			2 => 2,
			4 => 4,
			6 => 6,
			8 => 8,
			10 => 10,
			12 => 12,
			14 => 14,
			16 => 16,
			18 => 18,
			20 => 20,
			22 => 22,
			24 => 24
		)	
	));