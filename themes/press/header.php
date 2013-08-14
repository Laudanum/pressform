<?php
/**
 * @package WordPress
 * @subpackage laudanum
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/libs/krewenki-jquery-lightbox/css/lightbox.css" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php
wp_enqueue_script('jquery', get_bloginfo('stylesheet_directory').'/libs/krewenki-jquery-lightbox/jquery.lightbox.js', array('jquery'));
?>

<style type="text/css" media="screen">
</style>

<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>

<?php wp_head(); ?>
<script src="<?php bloginfo('stylesheet_directory'); ?>/libs/krewenki-jquery-lightbox/jquery.lightbox.js" type="text/javascript"></script>
</head>
<body <?php body_class(); ?>>
<div id="page">
<div id="mainnav">
  <?php
  if ( function_exists('wp_nav_menu') ) {
      wp_nav_menu("menu=main-menu&container_class=main-menu&menu_id=nav");
  }
  ?>
  <div class='extra description'><?php bloginfo ( 'description' ); ?></div>
  <div class='extra social'>
    <a href="http://www.facebook.com/pages/Pressform-Engineering-Pty-Ltd/196396364168?fref=ts" style="display:inline;"><img src="/wp-content/uploads/2013/02/f_logo.png" height="20px" width="20px"></a>
  </div>
</div>

<?php if ( is_front_page() ) { print "<div id=\"home\">"; } ?>
<div id="header" role="banner">
  <div id="headerimg">
<h1>
<a href="<?php echo get_option('home'); ?>/"><i><?php bloginfo('name'); ?></i></a>
</h1>
    <div class="description"><h2><?php 
    if (is_category()) { single_cat_title(); } 
    else if (is_page() && !is_front_page() ) { the_title(); } 
    ?></h2></div>
      
  </div>  
</div>
<?php if ( is_front_page() ) { print "</div>"; } ?>
<hr />