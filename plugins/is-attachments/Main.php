<?php
/**
 * @package is-attachments
 * @author Mr Snow
 * @version 0.885
 */
/*
Plugin Name: isEngine Attachments
Plugin URI: http://laudanum.net/wordpress
Description: 
Author: Mr Snow
Version: 0.885
Author URI: http://laudanum.net/mr.snow
*/
	

/*
	
	HISTORY
	0.886	Fix for attachments without metadata
	0.885	is_drawGallery supported. Also draws social video (vimeo only)
	0.884	Multiple YouTube attachments have separate thumbnails
	0.883a	disable proplayer for vimeo
	0.883	attachments retain their menuorder
	0.882	list downloads no longer expects a post id
	0.881	updates to get by role
	0.880	removing roles (so there are none) now works
	0.879	add poster to rel in listAttachments for videos
	0.878	fix list_attachments a->large
	0.877	rewrite the zoomsrc logic
	0.876	fix array check
	0.875	fix linksize bug when a->large does not exist
	0.874	fix linksize if its not contained in meta['sizes'] eg large when the src is less than large size
	0.873	repair social video parenting and roles errors
	0.872	add is_listDownloads method
	0.871	extend is_drawImageGallery to allow `caption` as a `linksize`

	TO DO
	
v	roles ( checkboxes ) eg : poster, low quality, masthead
x	cropper - jcrop http://wordpress.org/extend/plugins/scissors/ scissors only shows in 'library' not 'gallery'
-	number of attachments on attachment icon and in post lists
v	attachments as children of attachments
v	auto gallery
v	auto video
-	media tagging	//	get_attachment_taxonomies
v	social video
-	social video needs progress bar
-	geocodes
-	ratings
-	register json handler to do (for example) return image gallery, 
		youtube video, updated social stats, ratings
-	allow attachment load from posts 


	KNOWN BUGS

-	_get_attachments doesn't return attachments without meta

//	media_handle_sideload	??

//	get_attachment_taxonomies
//	wp_get_object_terms


	VIMEO -> longtail player
	PROPLAYER
		new ProPlayer > ContentHandler > addFileAttributes > VideoFactory > createVideoSource
		$videoURL = $this->contentHandler->getVideoUrl($match);
	
*/


	include_once ( dirname(__FILE__) . "/is-admin.php");
	include_once ( dirname(__FILE__) . "/is-options.php");
	include_once ( dirname(__FILE__) . "/is-library.php");
	include_once ( dirname(__FILE__) . "/is-socialvideo.php");
	include_once ( dirname(__FILE__) . "/is-widget.php");
	include_once ( dirname(__FILE__) . "/is-utils.php");
	
	
	function is_attachments_header() {
			
		print "
		<script type='text/javascript'>
//	path to ajax url
			var ajaxurl = '" . admin_url('admin-ajax.php') . "';
		</script>
		";
		
	}
	
	
	function is_attachments_footer() {
	
	}


	function is_attachments_menu() {
		if ( function_exists('add_submenu_page') )
			add_submenu_page('options-general.php', __('Attachments'), __('Attachments'), 'manage_options', 'is_attachments', 'do_is_attachments_options');
	}
	
	
	
	function is_widgets_init() {
		register_widget('AttachmentsWidget');
	}


	function is_attachments_init() {
//		add_settings_section('is_attachments_section', 'Extended attachment settings', 'is_attachments_section_callback_function', 'media');
//		add_settings_field('is_attachments_options[parent_menu]', 'Show attachment parent', 'is_attachments_settings_callback_function', 'media', 'is_attachments_section', array('label_for'=>'OI') );
//		register_setting('page', 'option', 'valiation callback');
		register_setting('is_attachments', 'is_attachments_options', 'is_attachment_validate_options');
		
//	this is a hack as the API is broken
//	http://wordpress.org/support/topic/299177
//	https://core.trac.wordpress.org/ticket/9296
//	check to see if we have options from the set

	}
	
	
	function is_attachment_validate_options($options) {
		return $options;
	}
	

/*
	function is_attachments_section_callback_function() {
     echo '<p>isEngine Attachments plugin</p>';
 }
 
	function is_attachments_settings_callback_function() {
		$checked = "true";
		$options = get_option('is_attachments_options');
//		print_r($options);
		
     
     // Mark our checkbox as checked if the setting is already true
		if ($options['parent_menu']) 
			$checked = " checked='checked' ";
		
 
		echo "<input {$checked} name='is_attachments_options[parent_menu]' type='checkbox' value='true' />";
		
	} 
*/

	function is_getAttachments1($post_id=null) {
		$time_start = microtime(true);
		
		if ( ! $post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		
		$args = array(
			'post_parent' => $post_id,
			'post_type' => 'attachment'
		);
		
		$results =& get_children($args);		
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		print count($results) . " in $time seconds\n";
		return $results;
	}
	

	function is_getAttachments2($post_id=null) {
		$time_start = microtime(true);
		
		if ( ! $post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		
		$args = array(
			'post_parent' => $post_id,
			'post_type' => 'attachment',
			'post_mime_type' => 'image'
		);
		
		$images =& get_children($args);

		$args = array(
			'post_parent' => $post_id,
			'post_type' => 'attachment',
			'post_mime_type' => 'video'
		);
		$videos =& get_children($args);
		$results = array(
			'images' => $images,
			'video' => $videos
		);
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		print count($results) . " in $time seconds\n";
		return $results;
	}
	
	
	function is_getAttachments($post_id=null, $mimetype=null, $limit=null) {
		$time_start = microtime(true);
		if ( ! $post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		
//				print "<!-- $post_id $mimetype attachments  -->";


		$results = array();
		
		if ( ! $mimetype ) {
			$images = _get_attachments($post_id, $mimetype='image');
			$audio = _get_attachments($post_id, $mimetype='audio');
			$video = _get_attachments($post_id, $mimetype='video');
			$other = _get_attachments($post_id, $mimetype=null, $not_mimetype=array('image','video','audio'));


			if ( count($images) ) {	
				$images = _append_child_attachments($images);
				$images = _update_image_paths($images);
				$results['images'] = $images;
			} else
				$results['images'] = array();

		
			if ( count($video) ) {	
				$video = _append_child_attachments($video);
				$results['video'] = $video;
			} else
				$results['video'] = array();
					
			if ( count($audio) )
				$results['audio'] = $audio;
			else
				$results['audio'] = array();

			if ( count($other) )
				$results['other'] = $other;
			else
				$results['other'] = array();

		} else {
			$results = _get_attachments($post_id, $mimetype);
			$results = _append_child_attachments($results);
			$results = _update_image_paths($results);
		}
		
//	TO FIX - roles
		if ( is_array ( $results['images'] ) ) {
			foreach ( $results['images'] as $a ) {
				if ( is_array ( $a->meta['roles'] ) ) {
					foreach( $a->meta['roles'] as $role=>$value )
						if ( $value )
							$results[$role] = $a;
					}
			}
		}
		
//			if ( $_REQUEST['debug'] )
//				print_r($results);
		
/*
		if ( is_array ( $results['video'] ) ) {
			foreach ( $results['video'] as $a ) {
				if ( is_array ( $a->meta['roles'] ) ) {
					foreach( $a->meta['roles'] as $role=>$value )
						if ( $value )
							$results[$role] = $a;
					}
			}
		}
*/		
		
		
//		if ( count($child_results) )
//			$results['children'] = $child_results;
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
//		print "<!-- " . count($results) . " in $time seconds -->";
		return $results;
	}
	
	
	function is_getAttachmentsByCaption($post_id=null, $string) {
		return is_getAttachmentsByExcerpt($post_id, $string);
	}
	
	
	function is_getAttachmentsByExcerpt($post_id=null, $string) {
		if ( empty($string) )
			return null;
	
	}
	
	
	function is_getAttachmentsByRole($post_id=null, $string) {
		if ( empty($string) )
			return null;

		$attachments = _get_attachments($post_id);
//			print $string;
//	    	print_r($attachments['$string']);

		$results = array();
		
		foreach ( $attachments as $a ) {
			if ( $a->meta['roles'] ) {
				foreach( $a->meta['roles'] as $role=>$value ) {
					if ( $role == $string ) {
						array_push($results, $a);
					}
				}
			}
		}
		
		$results = _update_image_paths($results);
		return $results;
		
/*		
    	if ( in_array($string, $attachments) ) {
	    	print_r($attachments['$string']);
    	
    		$roles = _update_image_paths(array($attachments['$string']));
    		if ( count($roles) < 2 )
    			$roles = $roles[0];
    		return $roles;
		} else {
			return null;
		}
*/
	}
	
	
	function is_getAttachmentsByTitle($post_id=null, $string) {
		if ( empty($string) )
			return null;
	
	}
	
	
	function is_getAttachmentsByDescription($post_id=null, $string) {
		return is_getAttachmentsByContent($post_id, $string);
	}
	
	
	function is_getAttachmentsByContent($post_id=null, $string) {
		if ( empty($string) )
			return null;
	
	}
	
	
	function getFirstAttachment($post_id=null, $children=true, $mimetype='image') {
		$attachments = _get_attachments($post_id, $mimetype=$mimetype, $not_mimetype=null, $limit=1);
		if ( count($attachments) && $children )
			$attachments = _append_child_attachments($attachments);
		return $attachments[0];
	}
	
	
	function getFirstImage($post_id=null, $children=true) {
		$attachment = _update_image_paths(array(getFirstAttachment($post_id, $children, $mimetype='image')));
		return $attachment[0];
	}
	
	
	function getFirstVideo($post_id=null, $children=true) {
		return getFirstAttachment($post_id, $children, $mimetype='video');
	}
	
	
	
	
    	function is_drawGallery($post_id=null, $size='medium', $mimetype=null, $linksize='large', $attachments=null) {
    		if ( ! $attachments ) {
    		  if ( $mimetype )
	    		  $attachments = is_getAttachments($post_id, $mimetype);
	    		else {
	    		  $attachments = _get_attachments($post_id, $mimetype=null, $not_mimetype=array('application','text'));
	    		  _update_image_paths($attachments);
	    		}
        }
    //    print_r($attachments);
        $i = 0;

    		foreach ( $attachments as &$a ) {
	        list($mime_type, $alt_title, $description) = is_get_attachment_meta($a);
    			if ( strpos($a->mime_type,'video') === 0 ) {
  			    $formatted = is_format_video($a, $size, $linksize);
  			  } else if ( strpos($a->mime_type,'image') === 0 ) {
  			    $formatted = is_format_image($a, $size, $linksize);
          }

    			echo "
    			<li id='attachment-$a->ID' class='gallery-node item-$i attachment-$a->ID parent-$a->parent type-$mimetype mimetype-$mime_type'>
            $formatted
    				<span class='title'>$a->title</span>
    				<span class='caption'>$a->caption</span>
    				<span class='description'>$description</span>
    			</li>
    			";
    			$i++;
    		}
    		return $attachments;
    	}
	
	
	function is_listAttachments($post_id=null, $mimetype='image', $attachments=null) {
		if ( ! is_array($attachments) )
			$attachments = is_getAttachments($post_id, $mimetype);
			
//		print "<!-- $mimetype attachments list -->";

		$i = 0;
		
		foreach ( $attachments as $a ) {
			$mime_type = str_replace('/','-',$a->mime_type);
			$alt_title = htmlspecialchars(strip_tags($a->title), ENT_QUOTES);
			$description = apply_filters("post_content", $a->description);
			
			if ( $a->meta['sizes']['large'] )
				$large = $a->meta['sizes']['large'];
			else
				$large = $a->meta;

//			$rel = $large['width'] . 'x' . $large['height'];
		//	$rel = json_encode($rel);
			
			if ( ! $a->large )
				$a->large = $a->src;

			if ( $a->poster )
				$rel = $a->poster->src;
			else
				$rel = "attachment-$a->ID";
			
//			print_r( $rel);
				
			echo "
			<li class='gallery-node item-$i parent-$a->parent type-$mimetype mimetype-$mime_type'>
				<a class='title' href='$a->large' title='$alt_title' rel='$rel'>
					$a->title
				</a>
				<span class='caption'>$a->caption</span>
				<span class='description'>$description</span>
			</li>
			";
			$i++;
		}
	}
	
	
		function is_listDownloads($post_id=null, $prefix=null, $suffix=null) {
			if ( ! $post_id ) {
				global $post;
				$post_id = $post->ID;
			}			
			
			$downloads = _get_attachments($post_id, $mimetype=null, $not_mimetype=array('image','video', 'audio'));
/*
print "<pre>";
print_r($downloads);
print "</pre>";	
*/			if ( count($downloads) ) {
				foreach ( $downloads as $a ) {
					$type = 'type-other';
   					$mime_type = str_replace('/','-',$a->mime_type);
		   			$alt_title = htmlspecialchars(strip_tags($a->description), ENT_QUOTES);
   					$title = apply_filters("post_title", $a->title);

					$rel = sanitize_title_with_dashes($title);
				
					$filetype = substr($mime_type, strrpos($mime_type, '-') + 1);

					print "
							<li class='download $type $mime_type $filetype'>
					";

					if ( $prefix ) {
						if ( $prefix == 'caption' ) {
							if ( $a->caption ) {
								print "<span class='caption'>$a->caption</span>";
							}
						} else if ( $prefix == 'date' ) {
//							
							if ( class_exists(DateTime) ) {
								$date = new DateTime($a->date);
								$date = $date->format('jS F Y');
							} else {
								$date = strtotime($a->date);
								$date = strftime('%e %B %Y', $date);
							}
							print "<span class='date'>$date</span>";
						}
					}

					print "
								<a class='title' href='" . $a->src . "' title='$alt_title' rel='$rel'>
									$title
								</a>
							</li>
					";
			
				}
			}
		}
	
	function is_drawImageGallery($post_id=null, $size='medium', $mimetype='image', $linksize='large', $attachments=null) {
	  return is_drawGallery($post_id, $size, $mimetype, $linksize, $attachments);
	}


  function is_get_attachment_meta($a) {
    $mime_type = str_replace('/','-',$a->mime_type);
		$alt_title = htmlspecialchars(strip_tags($a->title), ENT_QUOTES);
		$description = apply_filters("post_content", $a->description);
    return array($mime_type, $alt_title, $description);
  }

	
	function is_format_video($a, $size, $linksize) {
	  $formatted = is_socialFormatter($a, $size);
    return $formatted;	  
	}
	
	
	function is_format_image($a, $size, $linksize) {
	  list($mime_type, $alt_title, $description) = is_get_attachment_meta($a);

		if ( strpos($a->mime_type,'video') === 0 ) {
			$a->large = is_getSocialURI($a->large, $a->ID);
		}

			$zoom = $a->meta;
			$zoomsrc = $a->src;

	if ( $linksize == 'caption' ) {
		$zoomsrc = $a->caption;
		$a->caption = '';
		} else if ( is_array($a->meta['sizes']) ) {
			if ( is_array($a->meta['sizes'][$linksize]) ) {
			    $zoom = $a->meta['sizes'][$linksize];
				$zoomsrc = $a->$linksize;
			} else {
				$zoom = $a->meta;
				$zoomsrc = $a->src;
		  }
		} else if ( $linksize == 'src' ) {
		  $zoom = $a->meta;
		  $zoomsrc = $a->src;
		} else {
			$variables = get_object_vars($a);
			if ( in_array($linksize, array_keys($variables)) ) {
				$zoom = $a->meta;
			    $zoomsrc = $a->$linksize;
			}
		} 
	
		$rel = $zoom['width'] . 'x' . $zoom['height'];
		
		return "
      <a href='$zoomsrc' title='$alt_title' rel='$rel'>
				<img alt='$alt_title' lowsrc='" . $a->thumbnail . "' src='" . $a->$size . "' />
			</a>
		";
	}
	
	
	function _get_attachments($post_id, $mimetype=null, $not_mimetype=null, $limit=null) {
//	this doesn't return attachments without meta
//		print $post_id . "\n";
		global $wpdb;
		
		if ( ! $post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		
//				print "<!-- $post_id $mimetype _get_attachments -->";

		if ( strtoupper ($post_id) != 'ALL' )
			$posts = " AND $wpdb->posts.post_parent IN ($post_id) ";
			
/*	
		$query = "
			SELECT
				post_title as title,
				post_content as description,
				post_excerpt as caption,
				guid as src,
				post_mime_type as mime_type
			FROM $wpdb->posts
			WHERE post_parent = $post_id
			AND post_type = 'attachment'
			ORDER BY menu_order
		";
*/

		if ( $mimetype ) {
			if ( is_array($mimetype) ) {
				$mime = '';
				foreach ( $mimetype as $m )
					$mime .= " AND $wpdb->posts.post_mime_type LIKE '$m%' ";
			} else {
				$mime = " AND $wpdb->posts.post_mime_type LIKE '$mimetype%' ";
			}
		}
		
		if ( $not_mimetype ) {
			if ( is_array($not_mimetype) ) {
				$not_mime = '';
				foreach ( $not_mimetype as $m )
					$not_mime .= " AND $wpdb->posts.post_mime_type NOT LIKE '$m%' ";
			} else {
				$not_mime = " AND $wpdb->posts.post_mime_type NOT LIKE '$mimetype%' ";
			}
		}

//	can we get children and tags in here ?
		$query = "
			SELECT DISTINCT
				$wpdb->posts.ID as ID,
				$wpdb->posts.post_title as title,
				$wpdb->posts.post_date as date,
				$wpdb->posts.post_content as description,
				$wpdb->posts.post_excerpt as caption,
				$wpdb->posts.post_parent as parent,
				$wpdb->posts.menu_order as `order`,
				$wpdb->posts.guid as src,
				$wpdb->posts.post_mime_type as mime_type
			FROM $wpdb->posts
			WHERE 1
			$posts
			AND $wpdb->posts.post_type = 'attachment'
			$mime
			$not_mime
			ORDER BY `order`
		";
		if ( $limit )	
			$query .= " LIMIT $limit";
		
		$results = $wpdb->get_results($query);
		for ( $i = 0; $i < count($results); $i++ ) {
			$results[$i]->meta = get_post_meta($results[$i]->ID, '_wp_attachment_metadata', TRUE);
		}
		return $results;

	}
	
	
	function _append_child_attachments($attachments=array()) {
//	it would be good if this handled a multidimensional array being passed in
//	as we would get a speed up by doing _get_attachments and the insert once

		$parent_ids = array();
		foreach ( $attachments as $a ) {
			array_push($parent_ids, $a->ID);
		}

//	here are the attachments
		$child_results = _get_attachments(implode(',', $parent_ids));
//		$child_results = _update_image_paths($child_results);
		
		
//	walk the arrays looking for the parents
//	child is passed by reference allowing it to be updated - DOES THIS WORK IN PHP4 ?
		foreach ( $child_results as &$child ) {
			
			foreach ( $attachments as &$a ) {
//	which parent ($a) is this child for ?
				if ( $child->parent == $a->ID ) {
					if ( is_array($a->children) )
						array_push($a->children, $child);
					else
						$a->children = array($child);

//	insert each child in as its role's as well
					if ( $child->meta['roles'] ) {
						foreach( $child->meta['roles'] as $role=>$value )
							if ( $value )
								$a->$role = $child;
//	pop posters in where images are missing
						if ( $child->meta['roles']['poster'] ) {
							if ( is_array($child->meta['sizes']) ) {
							foreach ( $child->meta['sizes'] as $size=>$src )
								if ( ! $a->meta['sizes'][$size] ) {
/*	from wp core - 
	./wp-includes/media.php:126:function image_downsize($id, $size = 'medium') {
	list( $img_src, $width, $height ) = image_downsize($id, $size);
*/
//									$a->$size = wp_get_attachment_url($child->ID);
									list( $img_src, $width, $height ) = image_downsize($child->ID, $size);
									$a->$size = $img_src;
									$a->meta['sizes'][$size] = $a->$size;
								}
							}
						}
						
					}
					break;
				}
			}

		}
		
//		print_r($attachments);
		return $attachments;
	
	}
	
	
	function _update_image_paths($images) {
		foreach ( $images as $a ) {
			$dir = substr($a->src, 0, strrpos($a->src, '/'));

			if ( is_array( $a->meta['sizes'] ) ) {
				foreach ( $a->meta['sizes'] as $key => $value ) {
					if ( ! $a->$key )
						$a->$key = $dir . '/' . $value['file'];
				}
			}
			if ( ! $a->large )
				$a->large = $a->src;
			if ( ! $a->medium )
				$a->medium = $a->large;
			if ( ! $a->thumbnail )
				$a->thumbnail = $a->medium;
		}
		return $images;
	}


	function ajax_is_attachments() {
		$post_id = $_REQUEST['id'];
		$type = $_REQUEST['type'];
	
		if ( empty($post_id) )
			return;
		
//		print $post_id;
		is_drawImageGallery($post_id, 'medium', $type);		
		
//	print "OI " . $_REQUEST['string'];
	
//		print $r->post_title;
//		print json_encode(item);
//		array_push($response, $item);

//	need to encode title to preserve comma's
	
//	print '{' . json_encode($response) . '}';
		return;
	}
	
	

	if ( is_admin() ) {
		add_action('admin_footer', 'is_attachments_footer');
		add_action('admin_head', 'is_attachments_header');
		add_action('admin_menu', 'is_attachments_menu');
		add_action('admin_init', 'is_attachments_init');

		add_filter('attachment_fields_to_edit', 'is_fields_to_edit_date', 10, 4);
		add_filter('attachment_fields_to_edit', 'is_fields_to_edit_parent', 10, 4);
		add_filter('attachment_fields_to_edit', 'is_fields_to_edit_children', 10, 4);
		add_filter('attachment_fields_to_edit', 'is_fields_to_edit_roles', 10, 4);
		add_filter('attachment_fields_to_edit', 'is_fields_to_edit_description', 10, 4);
		add_filter('attachment_fields_to_save', 'is_fields_to_save', 11, 2);

//	add social tab and perhaps parents
		add_filter('media_upload_tabs', 'is_upload_tab');
//		add_action('media_upload_social_video', 'media_upload_social_video');


//		add_filter('posts_where', 'is_attachments_admin_posts_where');
	}
	
 	add_action('widgets_init', 'is_widgets_init');

//	add_action('wp_ajax_is_attachments', 'ajax_is_attachments');
//	add_action('wp_ajax_nopriv_is_attachments', 'ajax_is_attachments'); 
//	add_action('wp_print_scripts', 'is_attachments_header');
 

?>
