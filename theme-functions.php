<?php

/**
 * Some useful function commonly used in Wordpress
 *
 * @author Adam Turtle <adam@adamturtle.com>
 * @copyright Copyright 2012, Adam Turtle
 * @version 1.0
 * @since 15.11.2011
 *
 * Contents
 * - tf_get_twitter
 * - tf_random_image
 * - tf_gravity_forms
 * - tf_disable_autop
 * - tf_image_resize
 * - tf_cols_shortcode
 */


/*	=TWITTER
------------------------------------*/

function tf_get_twitter($args){

	$default_args = array(
		'username' 				=> 'adamturtle',
		'limit' 					=> 1,
		'filter_reply' 		=> true,
		'relative_date'		=> true,
		'follow_link'			=> false
	);
	
	$args = array_merge($default_args, $args);
	
	// Plural
	function plural($num) {
		if ($num != 1)
	  	return "s";
	}
	
	// Relative Time
	function getRelativeTime($date) {
	
	  $diff = time() - strtotime($date);
	  if ($diff<60)
	          return $diff . " second" . plural($diff) . " ago";
	  $diff = round($diff/60);
	  if ($diff<60)
	          return $diff . " minute" . plural($diff) . " ago";
	  $diff = round($diff/60);
	  if ($diff<24)
	          return $diff . " hour" . plural($diff) . " ago";
	  $diff = round($diff/24);
	  if ($diff<7)
	          return $diff . " day" . plural($diff) . " ago";
	  $diff = round($diff/7);
	  if ($diff<4)
	          return $diff . " week" . plural($diff) . " ago";
	  return "on " . date("F j, Y", strtotime($date));
	}
	
	
	if($args['username']) {

		$feedURL 	= file_get_contents('http://twitter.com/statuses/user_timeline/'.$args['username'].'.json');
		$tweets = json_decode($feedURL);		
				
		if($tweets){
			// Initialize output
			$o = null;
				
			$o.= '<ul id="twitter_feed">' . "\n";		
			
			$tweet_count = 1;
			for( $i=0; $i<$args['limit'];$i++):
				
				$tweet = $tweets[$i];
				$tweet_text = $tweet->text;								
				
				if($tweet_text){
																	
					$class = 'tweet-' . $tweet_count;
					$tweet_count++;
					// Add class to first item
					if($i == 0) $class.= " first";
					// Add class to last item
					if($i == ($args['limit'] - 1)) $class.= " last";
	
					// Check if tweet is a reply
					if( $args['filter_reply'] == true ){
						$pos = strpos($tweet_text, '@');
						if ($pos === 0) {
							$args['limit']++;
							$tweet_count--;
							continue;
						}
					}				
		
					// Begin output
					$o.= "<li class=\"$class\">";
						
					// Convert urls to links
					$tweet_text = make_clickable($tweet_text);
					// Convert hashtags to links
					$tweet_text = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $tweet_text);
					// Convert usernames to links
					$tweet_text = preg_replace('/(^|\s)@(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/#!/\2">@\2</a>', $tweet_text);
						
					$o.= '<span class="tweet">' . $tweet_text . '</span> ';
					$o.= '<span class="twitter_datestamp">';
					$o.= '<a href="http://www.twitter.com/#!/' . $tweet->user->screen_name . '/status/' . $tweet->id_str . '">';
					if($args['relative_date'] == false){
						$o.= date('jS F, Y', $tweet->created_at);		
					}
					else {
						 $o.= getRelativeTime($tweet->created_at);
					}
					$o.= '</a>';
					$o.= "</span>";
					if($args['follow_link'] == true){
					 	$o.= '<p class="twitterFollow"><a href="http://www.twitter.com/'.$args['username'].'" title="';
					 	$o.= __('Follow '.get_bloginfo('name').' on Twitter');
					 	$o.= '">';
					 	$o.= __('Follow @'.$args['username'].' on Twitter &rarr;</a></p>'."\n");
					}			
					$o.= "</li>" . "\n";
				} // end if $tweet_text
			endfor;
			$o.= "</ul><!-- End #twitter_feed -->";			
			echo $o;
		}
		else {
			echo 'No tweets. Either username is wrong or user has protected their tweets.';
		}
	}
	else {
		_e('Please enter your Twitter username in your theme options to enable this feature.');
	}
}


/*	=RANDOM IMAGE FROM DIRECTORY
------------------------------------*/

// Display a random background
function tf_random_image($args = null){

	$default_args = array(
		'path' => '/library/images/backgrounds/',
		'include' => array("jpg", "jpeg", "png", "gif")
	);
	
	$args = array_merge($default_args, $args);

	$path_root 	= STYLESHEETPATH;
	$path_theme = $args['path'];
	// If no trailing slash, add one
	if( substr($path_theme, -1) != '/' ) $path_theme.= '/';
	
	$dir = $path_root . $path_theme;
	$folder = opendir($dir);

	$pic_types = $args['include'];

	$index = array();

	// Add each image in folder to array
	while ($file = readdir ($folder)) {
	  if(in_array(substr(strtolower($file), strrpos($file,".") + 1),$pic_types))
		{
			// Ignore images that begin with underscore ()
			if(substr($file,0,1) != "_"){
				array_push($index,$file);			
			}
		}
	}
	closedir($folder);

	if(!empty($index)){

		$totalImages = count($index);
		$random = rand(0, ($totalImages-1));

		// Get image file info
		$info = pathinfo($index[$random]);
		
		// Add background image to page
		return get_bloginfo('stylesheet_directory') . $path_theme . $index[$random];
	}
}


/*	=GRAVITY FORMS CROSS-BROWSER PLACEHOLDER
------------------------------------*/

function tf_gravity_forms($position, $form_id){

	// Create settings on position 25 (right after Field Label)
	
	if($position == 25){
	?>

	<li class="admin_label_setting field_setting" style="display: list-item; ">
	<label for="field_placeholder">Placeholder Text
	
	<!-- Tooltip to help users understand what this field does -->
	<a href="javascript:void(0);" class="tooltip tooltip_form_field_placeholder" tooltip="&lt;h6&gt;Placeholder&lt;/h6&gt;Enter the placeholder/default text for this field.">(?)</a>
	
	</label>
	
	<input type="text" id="field_placeholder" class="fieldwidth-3" size="35" onkeyup="SetFieldProperty('placeholder', this.value);">
	
	</li>
	<?php
	}
}
add_action("gform_field_standard_settings", "tf_gravity_forms", 10, 2);

/* Now we execute some javascript technicalitites for the field to load correctly */
function tf_gform_editor_js(){ ?>
	<script>
	//binding to the load field settings event to initialize the checkbox
	jQuery(document).bind("gform_load_field_settings", function(event, field, form){
		jQuery("#field_placeholder").val(field["placeholder"]);
	});
	</script>

	<?php
}
add_action("gform_editor_js", "tf_gform_editor_js");

/* We use jQuery to read the placeholder value and inject it to its field */
function tf_gform_enqueue_scripts($form, $is_ajax=false){?>
	<script>

	jQuery(function(){
	<?php
	/* Go through each one of the form fields */
		foreach($form['fields'] as $i=>$field){
		/* Check if the field has an assigned placeholder */
			if(isset($field['placeholder']) && !empty($field['placeholder'])){
			/* If a placeholder text exists, inject it as a new property to the field using jQuery */ ?>
				jQuery('#input_<?php echo $form['id']?>_<?php echo $field['id']?>').attr('placeholder','<?php echo $field['placeholder']?>');
				<?php
			}
		}
	?>
	});
	</script>
	<?php
}
add_action('gform_enqueue_scripts',"tf_gform_enqueue_scripts", 10, 2);


/*	=DISABLE AUTOP
------------------------------------*/

function tf_disable_autop($content) {
	$new_content = '';
	$pattern_full = '{(\[raw\].*?\[/raw\])}is';
	$pattern_contents = '{\[raw\](.*?)\[/raw\]}is';
	$pieces = preg_split($pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE);
	
	foreach ($pieces as $piece) {
		if (preg_match($pattern_contents, $piece, $matches)) {
	  	$new_content .= $matches[1];
	  } else {
	  	$new_content .= wptexturize(wpautop($piece));
		}
	}
	return $new_content;
}
remove_filter('the_content', 'wpautop');
remove_filter('the_content', 'wptexturize');
add_filter('the_content', 'tf_disable_autop', 99);


/*	=NATIVE WP IMAGE RESIZING
------------------------------------*/

function tf_image_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false, $jpeg_quality = 90 ){

	// this is an attachment, so we have the ID
	if ( $attach_id ) {

		$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
		$file_path = get_attached_file( $attach_id );

		// this is not an attachment, let's use the image url

	} else if ( $img_url ) {

			$file_path = parse_url( $img_url );
			$file_path = ltrim( $file_path['path'], '/' );
			//$file_path = rtrim( ABSPATH, '/' ).$file_path['path'];

			$orig_size = getimagesize( $file_path );

			$image_src[0] = $img_url;

			$image_src[1] = $orig_size[0];

			$image_src[2] = $orig_size[1];

		}

	$file_info = pathinfo( $file_path );
	$extension = '.'. $file_info['extension'];

	// the image path without the extension
	$no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];

	$cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;

	// checking if the file size is larger than the target size
	// if it is smaller or the same size, stop right here and return
	if ( $image_src[1] > $width || $image_src[2] > $height ) {

		// the file is larger, check if the resized version already exists (for crop = true but will also work for crop = false if the sizes match)
		if ( file_exists( $cropped_img_path ) ) {

			$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );

			$vt_image = array (
				'url' => $cropped_img_url,
				'width' => $width,
				'height' => $height
			);

			return $vt_image;
		}

		// crop = false
		if ( $crop == false ) {

			// calculate the size proportionaly
			$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
			$resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;
			// checking if the file already exists
			if ( file_exists( $resized_img_path ) ) {

				$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

				$vt_image = array (
					'url' => $resized_img_url,
					'width' => $proportional_size[0],
					'height' => $proportional_size[1]
				);
				return $vt_image;
			}
		}

		// no cached files - let's finally resize it
		$new_img_path = image_resize( $file_path, $width, $height, $crop, $jpeg_quality );
		$new_img_size = getimagesize( $new_img_path );
		$new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

		// resized output
		$vt_image = array (
			'url' => $new_img,
			'width' => $new_img_size[0],
			'height' => $new_img_size[1]
		);

		return $vt_image;
	}

	// default output - without resizing
	$vt_image = array (
		'url' => $image_src[0],
		'width' => $image_src[1],
		'height' => $image_src[2]
	);

	return $vt_image;
}


/*	=PLACEHOLDER IMAGE
------------------------------------*/

// Placeholder images
function tf_placeholder_image($args = null){

	$default_args = array(
		'width'				=> 60, 
		'height' 			=> '4:3',
		'text' 				=> 'Test Image', 
		'class' 			=> null, 
		'url' 				=> null, 
		'rel' 				=> null,
		'bg_colour'		=> 'CCC',
		'text_colour' => '888'
	);
	
	$args = array_merge($default_args, $args);
	
	$image_host = "http://placeholder.phpfogapp.com";
	
	$url = $image_host . "/" . $args['width'] . "x" . $args['height'] . "/" . $args['bg_colour'] . "/" . $args['text_colour'] . "&text=" . $args['text'];
	if($args['url']){
		return '<img class="'. $args['class'] .'" width=' . $args['width'] . ' height="'. $args['height'] .'" src="' . $url . '" rel="#'. $args['rel'] .'" alt="'. $args['text'] .'" />';
	}
	else {
		return '<img class="'. $args['class'] .'" width=' . $args['width'] . ' height="'. $args['height'] .'" src="' . $url . '" />';
	}
}


/*	=COLUMNS SHORTCODE
------------------------------------*/

function tf_cols_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
	'position' => '', 
	'width' => '6',
	), $atts ) );

	$output = '<div class="grid_' . $width;
	if($position){
		switch ($position) {
	  	case 'last':
	    	$position = ' omega';
	      break;
	    case 'first':
	      $position = ' alpha';
	      break;
	    default:
	      $position = NULL;
		}
		$output .= $position;
	}
	$output .= '">' . do_shortcode($content) . '</div>';
	return $output;
}
add_shortcode('col', 'tf_cols_shortcode');


/*	=GET SCREENSHOT
------------------------------------*/

function tf_get_screenshot($args = null){

	$default_args = array(
		'width'				=> null, 
		'url' 				=> null
	);	
	$args = array_merge($default_args, $args);
		
	return "http://s.wordpress.com/mshots/v1/" . urlencode($args['url']) ."?w=$width";
}