<?php

/*
Plugin Name: isEngine order posts
Description: Drag and drop sorting of posts menu_order field
Author: Wintermoss Snow
Version: 0.44
Author URI: http://laudanum.net/wordpress


Order posts for isengine attachments

-	drag and drop
-	ajax updating

CHANGELOG
0.44
-	disable autosave . users now have to do it manually from the menu bar .
0.43
-	partial switch to jQuery as IE8 has patch support for getElementsByTagName
0.42
-	enable ghosting ( fixed in scriptaculous or ie8 ) . suppress wordpress errors ( screw up ajax response )
0.41
-	disable sorting for media library ( its built in )
0.40
-	name change ( hyphen from underscore )
0.39
-	update to understand when flutter filters posts ( filter-posts=1 or custom-write-panel-id=3 )

0.38
-	fixed include logic (include none is the same as exclude none)

0.37
-	fixed backward compat for settings_fields

0.36
-	fixed backward compat for register_settings
-	return all (not 0-15) filtered posts

0.35
-	added options pages ( in progress )

0.34
-	fixed scriptaculous ghosting bug in IE (is_orderposts.js)

0.33
-	fixed break bug in firefox

0.32
-	fixed wordpress in subdir

0.31
-	fixed order filter

0.3
now supports Wordpress 2.5 including
-	media gallery
-	edit pages
-	edit posts

ajax from
http://www.zenofshen.com/2008/01/08/scriptaculous-ajax-sortable-lists-tutorial/
http://www.gregphoto.net/index.php/2007/01/16/scriptaculous-sortables-with-ajax-callback/


TODO TOFIX

order categories
this guy adds a column called term_order into terms .
http://wordpress.org/extend/plugins/my-category-order/

here he is adding the column
	$query = mysql_query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'") or die(mysql_error());
	
	if (mysql_num_rows($query) == 0) {
		$wpdb->query("ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'");
	}

we can *try* ( though he thinks it wont work ) useing the filter `list_cats'
``quote
list_cats 
called for two different purposes:
	1.	the wp_dropdown_categories function uses it to filter the show_option_all and show_option_none arguments (which are used to put options "All" and "None" in category drop-down lists). No additional filter function arguments.
	2.	the wp_list_categories function applies it to the category names. Filter function arguments: category name, category information list (all fields from the category table for that particular category).
list_cats_exclusions''

better would be to override wp_list_categories or create is_list_categories

-	multiple select
-	order categories
-	add option to sort by menu_order by default
-	add option to restrict sorting to filtered posts
v	display more than 15 results in filtered admin views

*/



	global $orderposts, $plugin_name, $plugin_description;
	$plugin_name = 'isEngine Order Posts';
	$plugin_description = "Order posts allows the drag and drop sorting of posts, 
		pages and attachments from their various list views in Wordpress's 
		administration interface. Updates to posts menu_order field are
		achieved using AJAX. Use this page to determine if this plugin should
		override the default ordering of posts and or pages (usually posts and 
		pages are ordered by ID).
	";
	
	if ( ! function_exists('get_bloginfo') ) {
		require_once('../../../wp-config.php');
		require_once('../../../wp-admin/admin.php');
	}

	if ( ! function_exists('is_add_upload_tabs_sorter') ) {
		require_once('functions.php');
	}
	
	$orderposts = get_bloginfo('wpurl') . "/wp-content/plugins/is-orderposts";
	
	if ( isset($_GET['cat_ID']) ) {
		_cat_sorter_page($_GET['cat_ID']);
	}
	
//	add order tab in uploads section
//	add_filter('wp_upload_tabs', 'is_add_upload_tabs_sorter');
//	wp2.5
//	add_filter('media_upload_tabs', 'is_add_upload_tabs_sorter');
//	add_action('upload_files_order', 'is_upload_tab_order');
//	add_filter('media_upload_order', 'is_media_upload_order');
	
//	add an order column to the categories section
	add_filter('cat_rows','is_add_cat_rows_sorter');
	
//	add post order column to the edit posts

//	don't do the attachments as we do that from within the post
//	add_filter('manage_media_columns', 'is_manage_posts_columns');
//	add_action('manage_media_custom_column', 'is_manage_posts_custom_column');

//	this doesn't include the gallery pop up in the post
//	media-item-info
//	<div id="media-items">
//	<div id='media-item-4' class='media-item child-of-3 preloaded'><div id='media-upload-error-4'></div><div class='filename'></div><div class='progress'><div 

//	admin actions and filters
	add_action('admin_print_scripts', 'is_admin_print_scripts');
	add_action('edit_category_form', 'is_edit_category_form');

	add_action('admin_menu', 'is_order_admin_menu');
	add_action('wp_ajax_is_orderposts', 'ajax_is_orderposts');

	if ( is_admin() ) {

		$filtered = $_REQUEST['cat'];
		
		$filtered = false;
		if ( 
			isset($_REQUEST['cat']) ||
			isset($_REQUEST['custom-write-panel-id']) ||
			$_REQUEST['filter-posts'] == 1
		) {
			$filtered = true;
		}
		
		if ( $filtered && is_order_ok($filtered) ) {
			add_filter('manage_posts_columns', 'is_manage_posts_columns');
			add_action('manage_posts_custom_column', 'is_manage_posts_custom_column');
			add_filter('post_limits_request', 'is_posts_limit');
			
		}
//	supported in wp2.5
		add_filter('manage_pages_columns', 'is_manage_posts_columns');
		add_action('manage_pages_custom_column', 'is_manage_posts_custom_column');

		add_action('wp_head', 'is_admin_print_scripts');
		add_filter('posts_orderby', 'is_posts_orderby');
		wp_enqueue_script("is_init", get_bloginfo('wpurl') . "/wp-content/plugins/is-orderposts/js/is_orderposts.js", array("prototype","scriptaculous","scriptaculous-controls","scriptaculous-dragdrop"), 0.1);
	}


	function is_order_admin_menu() {
//	admin page
		if ( function_exists('add_options_page') ) {
			global $plugin_name;
			$page_title = $plugin_name;
			$menu_title = 'Order posts';
			$access = 'manage_categories';
			$function = 'is_order_posts_options';
			add_options_page($page_title, $menu_title, $access, __FILE__, $function);
		}
	}

	
	function is_order_posts_options() {
		global $plugin_name, $plugin_description;
		if ( function_exists('register_setting') ) {	
			register_setting('is_order_posts_options', 'is_order_posts_by');
			register_setting('is_order_posts_options', 'is_order_pages_by');
		
			register_setting('is_order_posts_options', 'is_order_categories');
			register_setting('is_order_posts_options', 'is_order_exclude');
		}
		
		if ( $_REQUEST['action'] == 'update' ) {
			update_option('is_order_posts_order_by', $_REQUEST['order_posts_by']);
			update_option('is_order_pages_order_by', $_REQUEST['order_pages_by']);
			update_option('is_order_categories', $_REQUEST['categories']);
			update_option('is_order_exclude', $_REQUEST['exclude']);
		}
		
		if ( get_option('is_order_posts_order_by') == 'true') {
			$posts_checked = 'checked';
			$opacity = 1.0;
		} else {
			$opacity = 0.2;
		}
		if ( get_option('is_order_pages_order_by') == 'true') 
			$pages_checked = 'checked';
		if ( get_option('is_order_exclude') == 'exclude') 
			$excl_checked = 'checked';
		else
			$incl_checked = 'checked';
					
		$categories = get_option('is_order_categories');

?>
		<div class="wrap">
			<h2><?php echo $plugin_name; ?></h2>
			<span class='description'><?php echo $plugin_description; ?></span>
			
			<form method="post" action="">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Order</th>
					<td>
						<input type="checkbox" name="order_posts_by" id="order_posts_by" value="true" <?php echo $posts_checked; ?> onclick='node = document.getElementById("is_order_posts"); if ( document.getElementById("order_posts_by").checked ) node.style.opacity = "1.0"; else node.style.opacity = "0.2";' /> Posts
						<br />
						<input type="checkbox" name="order_pages_by" value="true" <?php echo $pages_checked; ?> /> Pages	
					</td>
				</tr>
				<tr valign="top" id="is_order_posts" style="opacity : <?php echo $opacity; ?>">
					<th scope="row">Categories</th>
					<td><input type="text" name="categories" value="<?php echo $categories; ?>" /> <span class='setting-description'>(Comma separated list of category IDs)</span>
					<br />
					<input type="radio" name="exclude" value="include" <?php echo $incl_checked; ?> /> Include
					<br />
					<input type="radio" name="exclude" value="exclude" <?php echo $excl_checked; ?> /> Exclude
					</td>
				</tr>

<?php
	if ( function_exists('settings_fields') )
		settings_fields('is_order_posts_options');
	else {
		echo "<input type='hidden' name='option_page' value='$option_group' />";
		echo '<input type="hidden" name="action" value="update" />';
		wp_nonce_field("settings_fields_group-options");
	}
?>

			</table>

			<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</p>

			</form>
		</div>

<?php
	}	//	end function is_order_posts_options()


	function is_posts_limit($limit) {
		return "";
	}


	function is_posts_orderby($orderby) {
		global $wpdb;
#		print "\"$orderby\"<br />";
#		print "$wpdb->posts.menu_order ASC";
		return "$wpdb->posts.menu_order ASC";
	}
?>