<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>

<!-- page.php -->

	<div id="content" <?php if (get_the_ID() != 8) print 'class="narrowcolumn"'; ?> role="main">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
		<h2><?php the_title(); ?></h2>
			<div class="entry">
				<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>

				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>

			</div>
		</div>
		<?php if ( is_front_page() ) { ?>


		<h4>News</h4>
		<ol class="list aligned">
		<?php 
			$args = array(
				'post_type' => 'post',
				'category' => '8', // any parent
				); 
			$news = get_posts($args);
			if ($news) {
				foreach ($news as $newspost) {
					//setup_postdata($post);
					print "<li><a href=\"".get_permalink($newspost->ID)."\">".get_the_title($newspost->ID)."</a></li>";
					
					
				}
			}
		?>
		</ol>
		<br>

		<h4>Downloads</h4>
		<ol class="list aligned">
		<?php is_listDownloads($post->ID); ?>
		</ol>
		<br />
		
		<?php if ( wp_list_bookmarks('echo=0') ) : ?>
		<h4>Links</h4>
		<ol class="list aligned">
		<?php
			wp_list_bookmarks(array('title_li'=>0, 'categorize'=>0));
		?>
		</ol>
		<br/>
		<?php endif; ?>

		<?php } ?>
		<?php endwhile; endif; ?>
	<?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
	

	</div>
        <aside class="page" >
            <?php include('gallery-list.php'); ?>
        </aside>

	<?php if ( false ) { ?>
	<div id="laser"><a href="<?php print get_category_link(16); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/laser-cuttings-new.gif" width="125" height="125" alt="Laser Cutting" title="Laser Cutting"/><a/></div>
	<?php } ?>


<?php get_footer(); ?>