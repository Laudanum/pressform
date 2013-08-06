<?php
/*
Plugin Name: WP Paging
Plugin URI: http://www.devdevote.com
Description: Page numbers with many settings.
Author: Jens T&ouml;rnell
Version: 1.0a
Author URI: http://www.jenst.se
*/

$wp_paging = new wp_paging();

add_action('admin_menu', array($wp_paging, 'admin_add_menu'));
add_action('admin_head', array($wp_paging, 'admin_add_css'));
add_action('wp_head', array($wp_paging, 'front_add_css'));

if(isset($_POST['submitter']))
	$wp_paging->data_save();

if(isset($_POST['deleter']))
	$wp_paging->data_delete();

/**
* class wp_paging
**/
class wp_paging
{
	private static $select_types = array(
		'behind_color' => array(
			'Dark',
			'Darker',
			'Bright',
			'Brighter',
			'Grey'
		),
		'page_bkg' => array(
			'Bright',
			'Cyan',
			'Dark',
			'Green',
			'Orange',
			'Pink',
			'Purple',
			'Red',
			'Yellow'
		),
		'current_page_bkg' => array(
			'Cyan',
			'Bright',
			'Dark',
			'Green',
			'Orange',
			'Pink',
			'Purple',
			'Red',
			'Yellow'
		),
		'alignment' => array(
			'Left',
			'Center',
			'Right'
		),
		'page_of_position' => array(
			'Left',
			'Right'
		)
	);

	# Used by add_action
	public function admin_add_menu()
	{
		add_options_page('WP Paging', 'WP Paging', 10, 'wp_paging', array('wp_paging', 'options'));
	}
	
	# Used by add_action
	public function admin_add_css()
	{
		if(isset($_GET['page']) && $_GET['page'] == 'wp_paging')
		{
			echo '<link rel="stylesheet" href="' . $this->get_style_url('style.css') . '" type="text/css" media="screen" />';
		}
	}
	
	# Used by add_action
	public function front_add_css()
	{
		
		$options = get_option('wp_paging');
		$profile_name = 'default';
		$styles = $options['settings_profiles'][$profile_name]['enable_styles'];
		
		if((sizeof($options) <= 1) || $styles === true)
		{
			global $wp_query;
			$max_num_pages = $wp_query->max_num_pages;
			if($max_num_pages > 1)
			{
				echo '<link rel="stylesheet" href="' . $this->get_style_url('style.css') . '" type="text/css" media="screen" />';
			}
		}
	}
	
	# get_style_url
	private function get_style_url($filename)
	{
//  laudanum - works with symlinks
	  $plugin_name = array_pop(explode(DIRECTORY_SEPARATOR, dirname(__FILE__)));
	  return trailingslashit(WP_PLUGIN_URL.'/' . $plugin_name) . 'css/'.$filename;
//		return trailingslashit(WP_PLUGIN_URL.'/' . plugin_basename(__FILE__)) . 'css/'.$filename;
	}
	
	# Save admin options - EJ OPTIMAL?
	public function data_save()
	{
		$option_name = 'wp_paging';
		$profile_name = 'default';
		
		### Values to paginate_links
		# Input check
		$options['prev_next'] = (isset($_POST['prev_next'])) ? (bool)true : (bool)false;	
		$options['show_all'] = (isset($_POST['show_all'])) ? (bool)true : (bool)false;	
		$options['page_of'] = (isset($_POST['page_of'])) ? (bool)true : (bool)false;
		$options['enable_styles'] = (isset($_POST['enable_styles'])) ? (bool)true : (bool)false;
		$options['enable_gradients'] = (isset($_POST['enable_gradients'])) ? (bool)true : (bool)false;
		$options['enable_rounded'] = (isset($_POST['enable_rounded'])) ? (bool)true : (bool)false;
		
		# Input text
		if(isset($_POST['end_size'])) $options['end_size'] = (int)$_POST['end_size'];
		if(isset($_POST['mid_size'])) $options['mid_size'] = (int)$_POST['mid_size'];
		if(isset($_POST['prev_text']) && $_POST['prev_text'] != '') $options['prev_text'] = $_POST['prev_text'];
		if(isset($_POST['next_text']) && $_POST['next_text'] != '') $options['next_text'] = $_POST['next_text'];
		if(isset($_POST['page_text']) && $_POST['page_text'] != '') $options['page_text'] = $_POST['page_text'];
		if(isset($_POST['of_text']) && $_POST['of_text'] != '') $options['of_text'] = $_POST['of_text'];
		if(isset($_POST['separator_text']) && $_POST['separator_text'] != '') $options['separator_text'] = $_POST['separator_text'];

		# Select
		if(isset($_POST['page_of_position'])) $options['page_of_position'] = $_POST['page_of_position'];
		if(isset($_POST['behind_color'])) $options['behind_color'] = $_POST['behind_color'];
		if(isset($_POST['page_bkg'])) $options['page_bkg'] = $_POST['page_bkg'];
		if(isset($_POST['current_page_bkg'])) $options['current_page_bkg'] = $_POST['current_page_bkg'];
		if(isset($_POST['alignment'])) $options['alignment'] = $_POST['alignment'];
		
		$profiles['settings_profiles'][$profile_name] = $options;
		$profiles['settings_global']['current_profile'] = $profile_name;
		
		if ( get_option($option_name) )
			update_option($option_name, $profiles);
		else
			add_option( $option_name, $profiles );
	}
	
	# Delete admin options
	public function data_delete()
	{
		delete_option('wp_paging');
		delete_option('wp_paging_style');
	}
	
	# Creates HTML selects from arrays
	function select_from_array($select_array, $select_name, $saved_value)
	{
		$selected = '';
		$output = '';
		$output .= '<select name="'.$select_name.'">';
		for($i=0; $i<sizeof($select_array); $i++)
		{
			$new_value = strtolower($select_array[$i]);
			if($new_value == $saved_value)
				$selected = ' selected="selected"';
			$output .= '<option value="' . $new_value .'" name="' . strtolower($select_array[$i]) . '"' . $selected . '>' . $select_array[$i] . '</option>';
			$selected = '';
		}
		$output .= '</select>';
		return $output;
	}
	
	# Checkboxes - Returns checked if value
	private function checked_html($options, $value, $default)
	{
		$output = '';
		
		if(sizeof($options) < 1)
			$output = ($default === true) ? 'checked="checked"' : '';
		else
			$output = (isset($options[$value]) && $options[$value] === true) ? 'checked="checked"' : '';
		echo $output;
	}
	
	# Input text - Returns empty if not exists
	private function input_html($options, $value, $default)
	{
		$output = '';
		if(sizeof($options) < 1)
		{
			$output = $default;
		}
		elseif(isset($options[$value]))
			$output = $options[$value];
		echo $output;
	}
	
	# Admin options page
	public static function options()
	{
		$profile_name = 'default';
		$profiles = get_option('wp_paging');
		$options = $profiles['settings_profiles'][$profile_name];
		
		$options_types = self::$select_types;
		
		include 'wp-paging-settings.php';
	}
	
	public function get_base()
	{
		$url = get_pagenum_link(2);
		$output = '';
		
		for($i=strlen($url); $i>0; $i--)
		{
			$found = substr($url, -$i);
			if($found == 2)
			{
				$last = substr($url, -$i);
				$last_count = strlen($last);
				$last = substr($last, 1);
				$first = substr($url, 0, -$last_count);
				$output = $first . '%_%' . $last;
			}
		}
		return $output;
	}
	public function modify_first_number($page_nav, $current_num, $prev_next, $prev_text)
	{
		$page_row = ($prev_next === true) ? 2 : 1;
	
		if($current_num > 1)
		{
			$modified = explode('<li>', $page_nav);
			if($prev_next === true)
			{
				$next_prev_li = "<a class='page-numbers' href='".get_pagenum_link($current_num-1)."'>".$prev_text."</a></li>\n\t";
				$page_nav = str_replace($modified[1], $next_prev_li, $page_nav);
			}
			$first_li = "<a class='page-numbers' href='".get_pagenum_link(0)."'>1</a></li>\n\t";
			$page_nav = str_replace($modified[$page_row], $first_li, $page_nav);
		}
		return $page_nav;
	}
}

/**
 * paginate_links - args
 *
 * max_count, base, total, current, prev_next, prev_text, next_text, show_all, end_size, mid_size, type, format
 *
 * Unused
 * add_args, add_fragment
 **/
function get_wp_paging($args = '')
{
	global $wp_query;
	global $wp_paging;
	global $paged;
	
	$profile_name = 'default';
	$profiles = get_option('wp_paging');
	$options = $profiles['settings_profiles'][$profile_name];
	
	if(is_admin())
		$paged = (isset($_GET['paged'])) ? $_GET['paged'] : 1;
	else
		$paged = (isset($paged) && $paged != 0) ? $paged : 1;
	
	$url = (is_admin()) ? add_query_arg( 'paged', '%#%') : $wp_paging->get_base();
	$format = (is_admin()) ? "?page=%#%" : "%#%";
	
	# Defaults from pageinate function
	$defaults_paginate = array(
		'base' => $url,
		'format' => $format,
		'total' => $wp_query->max_num_pages,
		'current' => $paged,
		'prev_next' => true,
		'prev_text' => 'Prev',
		'next_text' => 'Next',
		'end_size' => 1,
		'mid_size' => 2,
		'show_all' => false,
		'type' => 'list'
	);
	
	# Defaults added after
	$defaults_additional = array(
		'enable_gradients' => true,
		'behind_color' => 'dark',
		'page_bkg' => 'bright',
		'current_page_bkg' => 'cyan',
		'enable_rounded' => true,
		'alignment' => 'left',
		'separator_text' => '...',
		'page_text' => 'Page',
		'of_text' => 'of',
		'page_of' => true,
		'page_of_position' => 'left',
		'before' => '',
		'after' => ''
	);
	
	$result = array_merge($defaults_paginate, $defaults_additional);
	
	# Defaults paginate are overwritten
	if(isset($options['prev_next']))
		$result['prev_next'] = ($options['prev_next'] === true) ? true : false;
	if(isset($options['page_of']))
		$result['page_of'] = ($options['page_of'] === true) ? true : false;
	
	# Add data from settings if exists
	if(isset($options['prev_text']))
		$result['prev_text'] = $options['prev_text'];
	if(isset($options['next_text']))
		$result['next_text'] = $options['next_text'];
	if(isset($options['page_text']))
		$result['page_text'] = $options['page_text'];
	if(isset($options['of_text']))
		$result['of_text'] = $options['of_text'];
	if(isset($options['show_all']))
		$result['show_all'] = $options['show_all'];
	if(isset($options['separator_text']))
		$result['separator_text'] = $options['separator_text'];
	if(isset($options['page_of_position']))
		$result['page_of_position'] = $options['page_of_position'];
	if(isset($options['end_size']))
		$result['end_size'] = $options['end_size'];
	if(isset($options['mid_size']))
		$result['mid_size'] = $options['mid_size'];
	
	# If style enabled, add CSS classes
	if($options['enable_styles'] === true)
	{
		if(isset($options['enable_gradients']))
			$result['enable_gradients'] = ($options['enable_gradients'] === true) ? true : false;
		if(isset($options['enable_rounded']))
			$result['enable_rounded'] = ($options['enable_rounded'] === true) ? true : false;
		if(isset($options['behind_color']))
			$result['behind_color'] = $options['behind_color'];
		if(isset($options['current_page_bkg']))
			$result['current_page_bkg'] = $options['current_page_bkg'];
		if(isset($options['page_bkg']))
			$result['page_bkg'] = $options['page_bkg'];
		if(isset($options['alignment']))
			$result['alignment'] = $options['alignment'];
	}
	
	# Settings order - args, options, defaults
	$r = wp_parse_args( $args, $result );
	extract( $r, EXTR_SKIP );
	
	$page_info = ($r['page_of'] === true) ? "\n\t<li><span class='page-info ".$r['page_of_position']."'>" . $r['page_text'] . " " . $r['current'] . " " . $r['of_text'] . " " . $r['total'] . "</span></li>" : "";
	
	# Adding CSS class from  selects
	$replaced = '';
	$replaced .= ' page-' . $r['page_bkg'];
	$replaced .= ' current-page-' . $r['current_page_bkg'];
	$replaced .= ' behind-' . $r['behind_color'];
	if($r['enable_gradients'] != false)
		$replaced .= ' fill-gradient';
	if($r['enable_rounded'] != false)
		$replaced .= ' shape-round';
	if($r['alignment'] != 'left')
		$replaced .= ' align-' . $r['alignment'];
	
	$wp_list_page_numbers = paginate_links($r);
	$wp_list_page_numbers = $wp_paging->modify_first_number($wp_list_page_numbers, $r['current'], $r['prev_next'], $r['prev_text']);
	
	# Place page-info left or right
	$wp_list_page_numbers = ($r['page_of_position'] === 'left') ? str_replace("<ul class='page-numbers'>", "<ul class='page-numbers'>" . $page_info, $wp_list_page_numbers) : str_replace("</ul>", $page_info . "</ul>", $wp_list_page_numbers);
	
	# Other replacements
	$wp_list_page_numbers = str_replace("<ul class='page-numbers", "<ul class='page-numbers" . $replaced, $wp_list_page_numbers);
	$wp_list_page_numbers = str_replace('>...<', '>'.$r['separator_text'].'<', $wp_list_page_numbers);
	
	# If more pages than one
	if($r['total'] > 1)
		return $r['before'] . "\n" . $wp_list_page_numbers . $r['after'];
}
function wp_paging($args = '')
{
	echo get_wp_paging($args);
}
?>