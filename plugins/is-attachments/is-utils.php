<?php
/**
 * @package is-attachments
 * @author Mr Snow
 * @version 0.8
 */
 
 
	function is_attachments_admin_posts_where($where='') {
//		print $where;
//		exit;
		return $where;	
	}



	function _get_filename($src) {
		return substr($src, strrpos($src, '/')+1);
	}

	function _get_parent_array() {
		global $wpdb;
		$results = array();

		$post_titles = "SELECT post_title, ID, post_type, post_parent FROM $wpdb->posts WHERE post_type != 'attachment' AND post_type != 'revision' ORDER BY post_title";
		$results =  $wpdb->get_results($post_titles);

		return $results;
	}
	

	function _get_attachments_array($id, $parent) {
		global $wpdb;
		$results = array();
	
//	check to see if this post is alreay the child of an attachment
		$parent_post = get_post($parent);
//	if it is then return our grandparent
		if ( $parent_post->post_type == 'attachment' )
			$parent = $parent_post->post_parent;

		$post_titles = "SELECT post_title, ID, post_type, post_parent FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent = '$parent' AND ID != '$id' ORDER BY menu_order";
		$results =  $wpdb->get_results($post_titles);
//		print $post_titles;
		return $results;
	}

class _attachment {
	public $post = array();
	public $image = array();

	function defaults() {
		$this->post['content'] = '';
		$this->post['post_title'] = 'empty title';
		$this->post['post_status'] = 'inherit';
//		$this->post['menu_order'] = 0;
//		$this->post['post_excerpt'] = '';
	
		$this->image['sizes'] = array();
	}
	
	function set($key, $value) {
		$this->post[$key] = $value;
	}
	
	function setImage($src, $size='src') {
		$this->image['sizes'][$size] = $src;
	}
}
	
?>