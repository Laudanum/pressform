<?php
/**
 * @package is-attachments
 * @author Mr Snow
 * @version 0.8
 */
 
 	function is_append_tinymce($content) {
 		return $content .= '
<script type="text/javascript" src="/cms/wp-content/plugins/is-attachments/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript" src="/cms/wp-content/plugins/is-attachments/tiny_mce/tiny_mce.js"></script>

<script type="text/javascript">
//		tinyMCE.init({
		jQuery().ready(function() {
			jQuery("textarea.tinymce").tinymce({
			script_url : "/cms/wp-content/plugins/is-attachments/tiny_mce/tiny_mce.js",
			mode : "specific_textareas",
			editor_selector: "tinymce",
			width : "460",
			plugins : "paste",
			theme : "advanced",
			theme_advanced_toolbar_location : "top",
			theme_advanced_buttons1 : "bold,italic,link,unlink,bullist,blockquote,pastetext,charmap,undo", 
			theme_advanced_buttons2 : "", 
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
//			onchange_callback : "updateGriffinWYSIWYG"
			setup : function(ed) {
				ed.onChange.add(function(ed) {
//					alert("oi 2");
					tinyMCE.triggerSave();
//					alert("oi 3");
				});
			}
			});


		});

		function updateGriffinWYSIWYG(inst) {
//			alert("oi 1");
			tinyMCE.triggerSave();
		}

</script>';
	}
 
 
	function is_fields_to_edit_children($form_fields, $post) {
		global $wpdb;
		$options = get_option('is_attachments_options');
		$form_fields = image_attachment_fields_to_edit($form_fields, $post);

/* ***	Attachment children	*** */
		$children = $wpdb->get_results($wpdb->prepare("SELECT ID, post_type, post_title, post_mime_type FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent = %d", $post->ID));
//		print_r($children);

//	function get_media_items( $post_id, $errors ) {
//	get_media_items checks if its an attachment and returns itself instead of its children
//	this prevents us from displaying the children in line AND from returning an attachment as a parent 
		if ( count($children) ) {
			$form_fields['children'] = array(
				'label' => __('Children'),
				'input' => 'html',
				'helps' => __('Attachments previously parented to this attachment.'),
				'html' => ''
			);
			
			foreach ($children as $c) {
				$form_fields['children']['html'] .= "<li><a href='" . get_bloginfo('wpurl') . "/wp-admin/media-upload.php?tab=gallery&post_id=$c->ID'>$c->post_title</a> [$c->post_mime_type]</li>";
			}
			
			$form_fields['children']['html'] = "<ul>" . $form_fields['children']['html'] ."</ul>";
		} else {
			$parent_post = get_post($post->post_parent);
			
			if ( $parent_post->post_type == 'attachment' ) {
//	our parent is an attachment so instead show 'back to parent'
				$form_fields['children'] = array(
					'label' => __('Parent'),
					'input' => 'html',
					'helps' => __(''),
					'html' => "<a href='" . get_bloginfo('wpurl') . "/wp-admin/media-upload.php?tab=gallery&post_id=" . $parent_post->post_parent . "'>Return to parent</a>."
				);
			}
			
		};

		return $form_fields;
	}

	
	function is_fields_to_edit_parent($form_fields, $post) {
		global $wpdb;
		$options = get_option('is_attachments_options');
		$form_fields = image_attachment_fields_to_edit($form_fields, $post);


/* *** Attachment parent *** */
		if ( $options['parent_menu'] ) {
			global $parent_array;

			if ( ! is_array($parent_array) )
				$parent_array = _get_parent_array();

			if ( is_array($parent_array) ) {
				$form_fields['post_parent'] = array(
					'label' => __('Parent'),
					'input' => 'html',
					'helps' => __('With which record is this file associated?'),
					'html'  => "
						<select style='width : 450px;'>
			
						</select>"
				);

				$form_fields['post_parent']['html'] = "<div class='table-nav'><select style='width : 450px;' id='attachments[$post->ID][post_parent]' name='attachments[$post->ID][post_parent]'><option value='0'>Select a parent</option><option value='0'>No parent</option>";

				if ( $options['parent_menu_attachments'] ) {
					$attachments_array = _get_attachments_array($post->ID, $post->post_parent);
					$title_suffix = '[attachment]';
					foreach ($attachments_array as $p) {
						if ( $post->post_parent == $p->ID )
							$selected = "selected";
						else
							$selected = "";
						$form_fields['post_parent']['html'] .= "<option value='$p->ID' $selected>$p->post_title $title_suffix</option>";
					}
				}

				foreach ($parent_array as $p) {
					if ( $post->post_parent == $p->ID )
						$selected = "selected";
					else
						$selected = "";
					
					$title_suffix = '';
					if ( $p->post_type == 'page' && $p->post_parent ) {					
						$pp = $p;
						
						$titles = array();
						while ( $pp->post_parent != 0 ) {
							$pp = get_post($pp->post_parent);
							array_push($titles, $pp->post_title);
						}
						$title_suffix = '[' . implode(array_reverse($titles), ' &#151; ') . ']';
					} 
					
					$form_fields['post_parent']['html'] .= "<option value='$p->ID' $selected>$p->post_title $title_suffix</option>";
				}
				$form_fields['post_parent']['html'] .= "</select></div>";
			}
		}
		
//		$form_fields['post_parent']['html'] = is_append_tinymce($form_fields['post_parent']['html']);
		
		return $form_fields;
	}

	
	function is_fields_to_edit_roles($form_fields, $post) {	
		global $wpdb;
		$options = get_option('is_attachments_options');
		$form_fields = image_attachment_fields_to_edit($form_fields, $post);

/* ***	Attachment roles	*** */
		if ( is_array($options['roles']) ) {
			$form_fields['roles'] = array(
				'label' => __('Roles'),
				'input' => 'html',
				'helps' => __('Tick any that apply.'),
				'html' => ""
			);
		
			$meta = wp_get_attachment_metadata($post->ID);
			foreach ( $options['roles'] as $r ) {
				$checked = '';
				if ( $meta['roles'][$r] )
					$checked = 'checked';
				$form_fields['roles']['html'] .= "<input type='checkbox' $checked value='true' name='attachments[$post->ID][roles][$r]' attachments[$post->ID][roles][$r]' /> <label for='$r'>" . ucfirst(implode(' ', explode('-', $r))) . "</label> ";
			}
		}
		
		return $form_fields;
	}
	
	
	function is_fields_to_edit_description($form_fields, $post) {
//	print_r($form_fields['post_content']);
		$form_fields['post_content']['label'] = 'Description+';
		$form_fields['post_content']['input'] = 'html';
		$form_fields['post_content']['html'] .= '

		<textarea type="text" style="display : block ! important; " class="tinymce" id="attachments[' . $post->ID . '][post_content]" name="attachments[' . $post->ID . '][post_content]">' .
			$form_fields['post_content']['value'] . '
		</textarea>

		';
		return $form_fields;
	}


	function is_fields_to_edit_date($form_fields, $post) {
//	print_r($form_fields['post_content']);
		$form_fields['post_date']['label'] = 'Date';
		$form_fields['post_date']['value'] = $post->post_date;
	
		return $form_fields;
	}


	function is_fields_to_edit($form_fields, $post) {
		global $wpdb;
		$options = get_option('is_attachments_options');
		$form_fields = image_attachment_fields_to_edit($form_fields, $post);

//		print_r($post);
//		http://whale.local/wordpress/wp-admin/media-upload.php?type=image&tab=gallery&post_id=5


//	images only		
		if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
		
//	test to see if the gd library is installed before offering to crop .
//			if ( ! function_exists('imagecreatefromstring') ) {
			if ( ! function_exists('function_exists') ) {
				$form_fields['image-crop'] = array();
				$form_fields['image-crop']['label'] = __('Recrop/resize');
				$form_fields['image-crop']['input'] = "html";
				$form_fields['image-crop']['html'] = __('The GD image library is not installed. Cropping is disabled.');
			} else {
				$form_fields['image-crop'] = array();
				$form_fields['image-crop']['label'] = __('Recrop/resize');
				$form_fields['image-crop']['input'] = "html";
//				$form_fields['image-crop']['helps'] = "Warning, edit 'full size' changes your originals.";
				$form_fields['image-crop']['html'] = "
			
				<input type='radio' name='attachments[$post->ID][image-crop]' id='image-crop-thumb-$post->ID' value='thumbnail' />
				<label for='image-crop-thumb-$post->ID'>Thumbnail</label>
				<input type='radio' name='attachments[$post->ID][image-crop]' id='image-crop-medium-$post->ID' value='medium' checked='checked' />
				<label for='image-crop-medium-$post->ID'>Medium</label>
				<input type='radio' name='attachments[$post->ID][image-crop]' id='image-crop-large-$post->ID' value='large' />
				<label for='image-crop-large-$post->ID'>Large</label>
				<input type='radio' name='attachments[$post->ID][image-crop]' id='image-crop-full-$post->ID' value='full' />
				<label for='image-crop-full-$post->ID'>Full size</label>
			
				<input type='button' class='button' value='Edit image' onclick='is_launchEditor(this.form, $post->ID, \"attachments[$post->ID][image-crop]\", \"" . get_bloginfo('wpurl') . "\")' />
<!-- load the editor in a new thickbox -->
<!-- see ajax example here http://jquery.com/demo/thickbox/ -->
<!--				<a class='' href='" . get_bloginfo('wpurl') . "'>Test</a> -->

				<input type='button' class='button' value='Auto size all' value='autosize' onclick='is_autosize(this, $post->ID, \"attachments[$post->ID][image-crop]\", \"" . get_bloginfo('wpurl') . "\");' />
			
			";
			}
//				return "<a href=\"javascript:window.open('/wp-content/plugins/is_attachments/is_attachment_editor.php?id=$id', 'cropwindow', 'status=true,toolbar=false,width=760,height=400');\">$icon</a>";
			
//				$form_fields['_final'] = "oath";

		}
	
		return $form_fields;
	}


	function is_fields_to_save($post, $attachment) {
		if ( isset($attachment['post_parent']) )
			$post['post_parent'] = $attachment['post_parent'];


//	try and format the date
		if ( ! empty($attachment['post_date']) ) {
//	returns false if it can't convert to a timestamp
			$date = strtotime($attachment['post_date']);

//	2009-10-23 10:15:43
			if ( $date )
				$post['post_date'] = date("Y-m-d H:i:s", $date);
		}


//	updating attachment meta
		$meta = wp_get_attachment_metadata($post['ID']);
		if ( isset($attachment['roles']) ) {
			$meta['roles'] = $attachment['roles'];
		} else {
			$meta['roles'] = '';
		}
		wp_update_attachment_metadata($post['ID'],  $meta);
		
		return $post;
	}
	
?>