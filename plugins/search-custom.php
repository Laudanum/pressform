<?php
/*
Plugin Name: Search Custom Fields
Plugin URI: http://guff.szub.net/search-custom-fields/
Description: Search post custom field values. Also provides for an alternative theme 'search' template: search-custom.php.
Author: Kaf Oseo (fixed by Wintermoss Snow)
Version: R1.beta1-wintermoss
Author URI: http://szub.net

	Copyright (c) 2006 Kaf Oseo (http://szub.net)
	Search Custom Fields is released under the GNU General Public License
	(GPL) http://www.gnu.org/licenses/gpl.txt

	This is a WordPress 2 plugin (http://wordpress.org).
*/

function szub_search_custom_join($join) {
	global $wpdb;
	if( is_search() && szub_is_search_key() ) {
		$join = " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
	}
	return $join;
}
add_filter('posts_join', 'szub_search_custom_join');

function szub_search_custom_where($where) {
	global $wp_query, $wp_version, $wpdb;
	if( !empty($wp_query->query_vars['s']) && szub_is_search_key() ) {
		$search = $wp_query->query_vars['s'];
		$key = $_GET['key'];
		$status = ($wp_version >= 2.1) ? 'post_type = \'post\' AND post_status = \'publish\'' : 'post_status = \'publish\'';
		$where = " AND $wpdb->postmeta.meta_key = '$key' AND $wpdb->postmeta.meta_value LIKE '%$search%' AND $status ";
	}
	return $where;
}
add_filter('posts_where', 'szub_search_custom_where');

function szub_search_custom_orderby($orderby) {
	global $wp_query, $wp_version, $wpdb;
//	if( !empty($wp_query->query_vars['s']) && szub_is_search_key() ) {
	if( szub_is_search_key() ) {

//	if its an era search, we should sort by date
//		if ( $_GET['key'] == 'Era' )
//			$orderby = " $wpdb->posts.ID ASC ";			
//			$orderby = " $wpdb->postmeta.meta_value ASC ";
//		else 
			$orderby = " $wpdb->posts.post_name ASC ";
	}
//	}
	return $orderby;
}
add_filter('posts_orderby', 'szub_search_custom_orderby');

	function szub_search_custom_template($template) {
	$old_template = $template;
	if( is_search() && szub_is_search_key() && file_exists(TEMPLATEPATH . '/search-custom.php') )
		$template = TEMPLATEPATH . '/search-custom.php';

	if( $template == "" ) {
//	mr.snow 28/03/2008
//	this creates an infinite recursion
//	get_query_template calls apply_filters('search_template') which again calls szub_search_custom_template which calls ...
	//	$template = get_query_template('search');
//	so instead do nothing and everything works anyway
	}

	return $template;
}
// the search template creates a stack overflow
add_filter('search_template', 'szub_search_custom_template');

function szub_is_search_key($key='') {
	if( isset($_GET['key']) ) {
		if( !empty($_GET['key']) || (!empty($key) && ($key = $_GET['key'])) )
			return true;
	}

	return false;
}
?>