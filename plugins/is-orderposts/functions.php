<?php

	global $cat_row_count;
	$cat_row_count = 0;
	
	
	function is_order_ok ($category=-1, $posts=true) {
	
		if ( ! $posts ) 
			if ( get_option('is_order_pages') == 'true' )
				return true;
			else
				return false;

		if ( get_option('is_order_posts_order_by') != 'true' )
			return false;
			
		$categories = explode(',', get_option('is_order_categories'));
		if ( get_option('is_order_exclude') == 'exclude' )
			$exclude = true;
		else
			$exclude = false;

		if ( $categories[0] == '' ) {
		//	no categories set so presume sorting is allowed
			return true;
		} else if ( count($categories) 
			&& 
			(
				( in_array($category, $categories) && ! $exclude )
				||
				(! in_array($category, $categories) && $exclude )
			)
		) {
			return true;
		} else {
			return false;
		}
	}


	function is_edit_category_form() {
		print "
		<p class=\"submit\"><input type=\"button\" class=\"button\" name=\"sort_cats\" value=\"Sort categories\" /></p>
		";
	}
	
	function is_add_cat_rows_sorter($cat_row) {
		global $all_atts, $post_atts, $action, $cat_row_count;
	
//		class='delete'>Delete</a></td>
/*
		if ( ! strstr($cat_row, "iss_order") ) {
//			$find = "/Delete<\/a><\/td>/i";
//			$replace = "Delete</a></td><td><a href='is_order'>Order</a></td>";

//	this is pre 2.5 and provides a post sorter, not a cat sorter
			$find = "/(<td.+)cat_ID=(\d+)' class='edit'>Edit<\/a><\/td>(.+<\/td>)/";
			$replace = "<td colspan=2 style=''>
				<table style='border : 0; padding : 2; margin : 0; width : 100%;'>
					<tr>\${1}cat_ID=\${2}' class='edit'>Edit</a></td>
						<td style='text-align : center;'>
							<a href='/wp-admin/edit.php?action=is_orderposts.php&cat_ID=\${2}' style='text-decoration : none ! important;'>Order</a>						
							<a href='/wp-admin/edit.php?cat=\${2}' style='text-decoration : none ! important;'>Order</a>						
							<a href='$orderposts/is_orderposts.php?cat_ID=\${2}' style='text-decoration : none ! important;'>Order</a>
						</td>
						\${3}
					</tr>
				</table>
			</td>";
			
			$cat_row = preg_replace($find, $replace, $cat_row, 1);
		}
*/
		$cat_row_count++;
//		$cat_row .= "<script type='text/javascript'>alert($cat_row_count)</script>";
		return $cat_row;
	}


	function is_add_upload_tabs_sorter($wp_upload_tabs) {
		global $wpdb, $all_atts, $post_atts, $action;

//		$wp_upload_tabs['order'] = array(__('Order'), 'upload_files', "is_upload_tab_order", $action ? 0 : $post_atts);
		if ( !isset($_REQUEST['post_id']) ) {
			return $wp_upload_tabs;
		}
		if ( intval($_REQUEST['post_id']) )
			$attachments = intval($wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent = %d", $_REQUEST['post_id'])));

		if ( $attachments > 1 )
			$wp_upload_tabs['order'] = sprintf(__('Order gallery (%s)'), "<span id='attachments-count'>$attachments</span>");

		return $wp_upload_tabs;
	}


	function is_upload_tab_order() {
		global $wpdb,$wp_query,$max_width, $post_id;
		
		
		require_once(TEMPLATEPATH . "/../../../wp-admin/includes/media.php");

		media_upload_header();

		$post_id = intval($_REQUEST['post_id']);
		if ( ! isset($post_id) ) {
			$post_obj = $wp_query->get_queried_object();
			$post_id = $post_obj->ID;
		}

		if ( ! isset($post_id) ) {
			print "<br />No ID";
			exit;
		}
	
		$query = "SELECT post_title, post_content, post_mime_type, guid, ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent = $post_id ORDER BY menu_order";

		$attachments = $wpdb->get_results($query);
		
		_attachment_sorter_page($attachments);
		
	}
	

//	this one is for wp2.5	
	function is_media_upload_order() {
		return wp_iframe('is_upload_tab_order', $errors );
	}
	
	
	function is_manage_posts_columns($defaults) {
//		$defaults['attachments'] = __('Attachments');
//		$defaults['order'] = __('Order');
		$defaults['order'] = 'Order <a href="javascript:is_update_posts();"><img class="is_orderposts_icon" border="0" align="absmiddle" src="' . get_bloginfo('wpurl') . '/wp-admin/images/marker.png" /></a>';
//		unset($defaults['comments']);
//		$defaults['ID'] = __('ID');
		
//		print_r($defaults);

		return $defaults;
	}


	function ajax_is_orderposts() {
		global $wpdb;
		
		$data = $_POST['data'];		
		$pairs = explode('&', $data);
		
		$step = 1;
		$i = 1;
		$msg = "";
		$ids = array();
		$when = '';

		foreach ( $pairs as $p ) {
			list($junk, $post_id) = explode('=', $p);
			$when .= " when $post_id then $i ";
			array_push($ids, $post_id);
			$msg .= "$post_id=$i&";
			$i += $step;
		}
		
		$query = "UPDATE $wpdb->posts SET menu_order = case ID $when end WHERE ID in (" . implode(',', $ids) . ")";
		$wpdb->query($query);
		$msg .= "\n$query";

		print "$msg";
		
		exit;
	}
	

	function is_manage_posts_custom_column($column_name) {
		global $wpdb, $post, $orderposts;
		print "<!-- column_name $column_name -->";
		if ( $column_name == 'order' ) {
			echo "<div style='text-align : left' id='menuorder_$post->ID'>$post->menu_order</div>
			<script type='text/javascript'>
//	compensate for the removal of the id on the parent table ( wp 2.5 )
				is_setuptable('menuorder_$post->ID');
//				new Ajax.InPlaceEditor('menuorder_$post->ID','$orderposts/is_orderposts_update.php?container=is_single',{rows:1,cols:3, callback: function(form, value) { return '$post->ID=' + escape(value) }});
			</script>
			";
		} else if ( $column_name == 'ID' ) {
			echo $post->ID;
		} else if ( $column_name == 'attachments' ) {
		
		}
	}


	function is_admin_print_scripts() {
		global $orderposts;
		
//		wp_enqueue_script("is_init", get_bloginfo('url') . "/wp-content/plugins/is_orderposts/js/is_init.js", array("prototype","scriptaculous"), 0.1);
/*	
		print "\n<script type='text/javascript' src='" . get_bloginfo('url') . "/wp-content/plugins/is_orderposts/js/is_init.js'></script>\n";
		print "\n<script type='text/javascript' src='" . get_bloginfo('url') . "/wp-content/plugins/is_orderposts/js/scriptaculous/lib/prototype.js'></script>\n";
		print "\n<script type='text/javascript' src='" . get_bloginfo('url') . "/wp-content/plugins/is_orderposts/js/scriptaculous/src/scriptaculous.js'></script>\n";
*/
		if ( ! is_admin() ) 
			return;
			
		print "
		<script type='text/javascript'>
//			var ajax_url = '$orderposts/is_orderposts_update.php';
			var orderposts_path = '$orderpost';
			var admin_path = '" . admin_url() . "';
			var ajax_url = '" . admin_url('admin-ajax.php') . "';
		</script>
		
		<style type='text/css'>
			form.inplaceeditor-form {
				width : 100px;
				margin : 0px;
			}
			form.inplaceeditor-form input[type='text'] { /* Input box */
			}
		
			form.inplaceeditor-form input[type='submit'] { /* The submit button */
			}

			form.inplaceeditor-form a { /* The cancel link */
				font-size : 0.6em;
				border : 1px gray solid;
				height : 20px;
				display : inline-block;
				color : black;
			}
			
			.inplaceeditor-saving { 
				background: url(/wp-content/plugins/is_orderposts/images/wheel-on.gif) bottom right no-repeat;
			}

			
		</style>
		";
			
	}


	function is_list_posts($posts) {

		foreach ( $posts as $a ) {
			print "
	<li id=\"post_$a->ID\">";
			if ( strstr($a->post_mime_type, "image") ) {
				$thumb = wp_get_attachment_thumb_url($a->ID);
				if ( isset ($thumb) ) {
					$img = $thumb;
				} else {
					$img = $a->guid;
				}
			
			print "<img src='$img' alt=\"$a->post_title\" title=\"$a->post_title\" />";
			} else {
				print "<span class='title'>$a->post_title</span><br />$a->post_mime_type";
			}
			print "</li>";
		}
	}
	
	
	function _cat_sorter_page($id=null) {
		if ( ! isset($id) ) {
//			print "No ID definded, exiting.";
//			exit;
		}

//		print "<h3>Sort Posts in Category $id</h3>";
				


	}
	

	function _attachment_sorter_page($attachments) { 
		global $orderposts;
		global $post_id;
		
		_print_header($orderposts);
		
		print "<div id='is_container'><ul id='isorderposts'>";
	
		is_list_posts($attachments);

		print "</ul></div>
		<img id='progress' src='$orderposts/images/blank.gif' alt='' width=16 height=16 align='absmiddle' />
		Click and drag to re-order your attachments. Changes will be saved automatically.
		<script type='text/javascript'>
//			addLoadEvent(is_orderposts_init);
			addLoadEvent(is_order_init);
			if ( typeof wpOnload == 'function'){
				wpOnload();
			}
		</script>
		";

	}


	
	function _print_header($orderposts) {
		global $orderposts;
	?>
		<link rel='stylesheet' href='http://mr.snow/wp-admin/css/media.css?version=2.5' type='text/css' />

		<script type='text/javascript'>
//			var ajax_url = "<?php echo $orderposts; ?>/is_orderposts_update.php";
			var orderposts_path = "<?php echo $orderposts; ?>";
		</script>
		
		<style type="text/css">
		
			#is_container {
				border-bottom : 1px solid #cccccc;
			}

			#isorderposts {
				position : relative;
				display : block;
				margin : 0;
				padding : 0;
				padding-top : 10px ! important;
				padding-left : 10px;
				height : 72px;
				width : auto;
				margin-left : auto;
				margin-right : auto;
			}

			#isorderposts li {
				font-size : 0.6em;
				width : 50px;
				height : 50px;
				border : 1px solid gray;
				float : left;
				margin : 5px 5px 5px 0;
				display : block;
				list-style : none;
				position : relative;
				background-color : beige;
			}			

			#isorderposts li img {
				width : 50px;
				height : 50px;
			}
			
			#isorderposts .title {
				font-size : 1.1em;
				font-weight : bold;
			}
			
		</style>

<?php	
	}
?>