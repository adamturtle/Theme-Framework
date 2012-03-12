<?php

class Theme_Framework
{

	/*	=TWITTER
	------------------------------------*/
	public function get_twitter($args){
	
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
						$o.= "<li class=\"clearfix $class\">";
							
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
				echo 'No tweets yet.';
			}
		}
		else {
			_e('Please enter your Twitter username in your theme options to enable this feature.');
		}
	}
	

	/*	=RANDOM IMAGE FROM DIRECTORY
	------------------------------------*/	
	public function random_image($args = null){
	
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
	

	/*	=NATIVE WP IMAGE RESIZING
	------------------------------------*/	
	public function resize( $attach_id = null, $img_url = null, $width, $height, $crop = false, $jpeg_quality = 90 ){
	
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
	public function placeholder($width, $height, $args = null){
	
		$default_args = array(
			'text' 				=> 'Test Image', 
			'class' 			=> null, 
			'url' 				=> null, 
			'rel' 				=> null,
			'bg_colour'		=> 'CCC',
			'text_colour' => '888'
		);
		
		$args = array_merge($default_args, $args);
	
		if( ! $width )	$width = 60;
		if( ! $height ) $height = ( ($width/4) * 3);
		
		$image_host = "http://placeholder.phpfogapp.com";
		
		$url = $image_host . "/" . $width . "x" . $height . "/" . $args['bg_colour'] . "/" . $args['text_colour'] . "&text=" . $args['text'];
		if($args['url']){
			return '<img class="'. $args['class'] .'" width=' . $width . ' height="'. $height .'" src="' . $url . '" rel="#'. $args['rel'] .'" alt="'. $args['text'] .'" />';
		}
		else {
			return '<img class="'. $args['class'] .'" width=' . $width . ' height="'. $height .'" src="' . $url . '" />';
		}
	}
	
	
	/*	=LOAD CSS
	------------------------------------*/	
	public function css( $styles = null ){
		if( $styles && ! is_admin() ){		
			foreach($styles as $style){
				$style = explode( '/', $style );
				$array_len = count( $style );
				$filename = str_replace('.css', '' , $style[$array_len - 1]);
				$path = implode('/', str_replace('.css', '' , $style));
				wp_register_style( $filename , get_template_directory_uri() . '/' . $path . '.css');
				wp_enqueue_style( $filename );		
			}
		}
	}
	
	
	/*	=LOAD JS
	------------------------------------*/
	public function js( $scripts = null ){
		if( $scripts && ! is_admin() ){		
			foreach($scripts as $script){
				$script = explode( '/', $script );
				$array_len = count( $script );
				$filename = str_replace('.js', '' , $script[$array_len - 1]);
				$path = implode('/', str_replace('.js', '' , $script));
				wp_register_script( $filename , get_template_directory_uri() . '/' . $path . '.js');
				wp_enqueue_script( $filename );		
			}
		}
	}
	
	
	/*	=PRETTY PRINT_R
	------------------------------------*/
	public function print_r( $input = null ){
		if( $input ){
			echo '<pre>' . print_r( $input, 1 ) . '</pre>';
		} else {
			return false;
		}
	}	

} // end class

$tf = new Theme_Framework();