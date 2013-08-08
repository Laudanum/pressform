<?php get_header(); ?>
<!-- template single product -->
    <div id="content">
        <div class="article-container page product narrowcolumn">
           <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <div class="page_content">
               <?php get_template_part( 'content', 'product' ); ?>
            </div>
            <?php endwhile; endif; ?>
        </div>
        <aside class="page" >
            <?php include('gallery-list.php'); ?>
        </aside>
    </div>
<?php get_footer(); ?>
