<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header();
?>
<script>
jQuery.noConflict();

jQuery(document).ready(function() {
	
				jQuery(".thumbnail[rel*='lightbox']").lightbox({
				    fitToScreen: true,
				    imageClickClose: false
		    	});

				})
</script>
<!-- archive.php-->
	<div id="content" class="widecolumn" role="main">
		
		<?php if (have_posts()) : ?>		

 	  <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
 	  <?php /* If this is a category archive */ if (is_category()) { ?>
		<!--<h2 class="pagetitle">Archive for the &#8216;<?php single_cat_title(); ?>&#8217; Category</h2>-->
 	  <?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
		<h2 class="pagetitle">Posts Tagged &#8216;<?php single_tag_title(); ?>&#8217;</h2>
 	  <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('F jS, Y'); ?></h2>
 	  <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('F, Y'); ?></h2>
 	  <?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		<h2 class="pagetitle">Archive for <?php the_time('Y'); ?></h2>
	  <?php /* If this is an author archive */ } elseif (is_author()) { ?>
		<h2 class="pagetitle">Author Archive</h2>
 	  <?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
		<h2 class="pagetitle">Blog Archives</h2>
 	  <?php } ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>
		
		<?php
		if ( ! $post->attachments ) {
			$post->attachments = is_getAttachments($post->ID);
		}
		?>
		
		<?php if ( category_description() ) { ?><div class="description"><?php echo category_description(); ?></div><?php } ?>
		<style>#imageData #bottomNavClose { background: url(<?php bloginfo('stylesheet_directory'); ?>/libs/krewenki-jquery-lightbox/images/closelabel.gif) no-repeat; }</style>
		<!-- brochures -->
		<?php while (have_posts()) : the_post(); ?>
		<?php
		if ( ! $post->attachments ) {
			$post->attachments = is_getAttachments($post->ID);
		}
		
		//print $post->ID;
		//print count($post->attachments);
		print "<ul class=\"downloads\">";
		is_listDownloads($post->ID);
		print "</ul>";
		?>
		<?php endwhile; ?>
		
		<!-- Products -->
		<ol class="images">

		<?php while (have_posts()) : the_post(); ?>
		<!--<div <?php post_class() ?>>-->
				<?php
				if ( ! $post->attachments ) {
					$post->attachments = is_getAttachments($post->ID);
				}
				
				if ( $post->attachments['poster'] ) {
					$thumbimg = $post->attachments['poster']->thumbnail;
					$largeimg = $post->attachments['poster']->large;
				}
				else if ( count($post->attachments['images']) ) {
					$thumbimg = $post->attachments['images'][0]->thumbnail;
					$largeimg = $post->attachments['images'][0]->large;
				}
				else {
					$thumbimg = get_bloginfo('stylesheet_directory') . "/images/blank.gif";
					$largeimg = $post->attachments['poster']->large;
				}
				
				$title = $post->attachments['poster']->title;
				?>
				<li>
				<a href="<?php print $largeimg; ?>" rel='lightbox' class="thumbnail" title="<?php the_title(); ?>" onmouseover="showName(<?php print $post->ID;?>)" onmouseout="hideName(<?php print $post->ID;?>)"><img src="<?php print $thumbimg; ?>" alt="<?php the_title(); ?>" title="<?php the_title(); ?>"/><br /><span class="prodname" id="name<?php print $post->ID; ?>" style="display:none;"><?php the_title(); ?></span></a>
				</li>

				
			<!--</div>-->

		<?php endwhile; ?>
		</ol>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>
	<?php else :

		if ( is_category() ) { // If this is a category archive
			printf("<h3 class='center'>Sorry, but there aren't any products in %s category yet.</h3>", single_cat_title('',false));
		} else if ( is_date() ) { // If this is a date archive
			echo("<h2>Sorry, but there aren't any posts with this date.</h2>");
		} else if ( is_author() ) { // If this is a category archive
			$userdata = get_userdatabylogin(get_query_var('author_name'));
			printf("<h2 class='center'>Sorry, but there aren't any posts by %s yet.</h2>", $userdata->display_name);
		} else {
			echo("<h2 class='center'>No posts found.</h2>");
		}
		//get_search_form();

	endif;
?>

	</div>

<?php //get_sidebar(); ?>
<script type="text/javascript">
	jQuery.noConflict();
	
	function showName(id) {
		jQuery("#name" + id).show();
	}
	function hideName(id) {
		jQuery("#name" + id).hide();
	}
</script>
<?php get_footer(); ?>
