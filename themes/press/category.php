<?php get_header(); ?>
<!-- template category  -->
<div id="content">
        <div class="article-container full_width category">
            <!--
              <?php
                global $wp_query;
                print_r($wp_query);
              ?>
            -->
          <?php if (have_posts()) : ?>
           
            <div class="page_content">
                <header>
                    <hgroup>                                
                        <h2><?php printf( __( '%s', 'twentyeleven' ), single_cat_title( '', false ) ); ?></h2>
                        <?php
                            $category_description = category_description();
                            if ( ! empty( $category_description ) )
                                echo apply_filters( 'category_archive_meta', '<div class="description">' . $category_description . '</div>' );
                        ?>
                    </hgroup>
                </header>
                
                <?php while (have_posts()) : the_post()  ?>
                
                    <?php 
                        $result = press_get_thumbnails_by_category();
                        
                        $link = get_permalink($result->ID);
                        $title = htmlentities(trim(strip_tags($result->post_title)), ENT_QUOTES);
                        $thumb = $result->post_thumbnail;
                     ?>
                    
                    <div class="entry clearfix">
                        <div class="image">
                            <?php if($thumb != ""): ?>
                                <a href="<?php print $link; ?>" title="<?php print $title; ?>"><?php print $thumb; ?></a>
                            <?php else: ?>
                                <a  class="no-image" href="<?php print $link; ?>" title="<?php print $title; ?>">No Image</a>
                            <?php endif; ?>
                        </div>
                        <div class="post_meta_data">
                           <h2><a href="<?php print $link; ?>" title="<?php print $title; ?>"><?php print $title; ?></a></h2>
                           <?php the_excerpt(); ?>
                        </div>
                    </div>
                    
                <?php endwhile;  ?>
            
            </div>
            <?php wp_paging(); ?>
            
            <?php else : ?>

                <article id="post-0" class="post no-results not-found">
                    <header class="entry-header">
                        <h1 class="entry-title"><?php _e( 'Nothing Found', 'twentyeleven' ); ?></h1>
                    </header><!-- .entry-header -->

                    <div class="entry-content">
                        <p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyeleven' ); ?></p>
                        <?php get_search_form(); ?>
                    </div><!-- .entry-content -->
                </article><!-- #post-0 -->

            <?php endif; ?>
        </div>
       
    </div>

<?php get_footer(); ?>
    

