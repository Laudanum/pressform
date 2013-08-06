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
	<ol id="nav">
	<?php 
	//pages to exclude
	$page = get_page_by_title('Join our mailing list');
	$subscribepageid = $page->ID;
	
	$page = get_page_by_title('Home');
	$homepageid = $page->ID;	
	?>
	<li><a href="<?php echo get_option('home'); ?>/">Home</a></li>
	<li id="text" title="<?php bloginfo('description'); ?>"><?php bloginfo('description'); ?></li>
<li id="text" style="left:870px;padding-top:7px; float:none !important;" ><a href="http://www.facebook.com/pages/Pressform-Engineering-Pty-Ltd/196396364168?fref=ts" style="display:inline;"><img src="/cms/wp-content/uploads/2013/02/f_logo.png" height="20px" width="20px"></a></li>
		<?php 
		//get the link for first list item for products
		$firstProduct = wp_list_categories('child_of=3&hide_empty=0&title_li=&echo=0'); 
		//get the first item
		preg_match_all("/<li class=\"cat-item(.*)<\/li>/Uis", $firstProduct, $matches);
		$firstProduct = $matches[0][0];		
		//get tthe link
		preg_match_all("/href=\"(.*)\"/Uis", $firstProduct, $itemlink); 		
		$firstProductLink = $itemlink[0][0];
				
		wp_list_categories('child_of=3&hide_empty=0&title_li=<a '.$firstProductLink.'>Products</a>'); 
		?>	
		<?php 
		$firstCapability = wp_list_categories('child_of=4&hide_empty=0&title_li=&echo=0'); 
		//get the first item
		preg_match_all("/<li class=\"cat-item(.*)<\/li>/Uis", $firstCapability, $matches);
		$firstCapability = $matches[0][0];		
		//get tthe link
		preg_match_all("/href=\"(.*)\"/Uis", $firstCapability, $itemlink); 		
		$firstCapabilityLink = $itemlink[0][0];
		
		wp_list_categories('child_of=4&hide_empty=0&title_li=<a '.$firstCapabilityLink.'>Capabilities</a>'); 
		?>
	<?php wp_list_pages('title_li=&exclude='.$homepageid.'' ); ?>
	</ol>
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