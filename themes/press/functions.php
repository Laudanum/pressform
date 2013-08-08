<?php

  add_filter('pre_get_posts', 'press_post_type');

  if ( function_exists('register_sidebar') ) {
    register_sidebar(array(
      'before_widget' => '<li id="%1$s" class="widget %2$s">',
      'after_widget' => '</li>',
      'before_title' => '<h2 class="widgettitle">',
      'after_title' => '</h2>',
    ));
  }

  if ( function_exists('is_posts_orderby') ) {
    add_filter('posts_orderby', 'is_posts_orderby');
  }

  if (function_exists('add_theme_support')) {
    add_theme_support('menus');
  }

  // query all post types 
  function press_post_type($query) {
    $post_types = get_post_types();
    if ( is_category() || is_tag()) {
      $post_type = get_query_var('product');
      if ( $post_type )
        $post_type = $post_type;
      else
        $post_type = $post_types;
      $query->set('post_type', $post_type);
      return $query;
    }
  }


  function press_get_thumbnails_by_category() {
      
      global $post;
      
      $result = $post;
      
  //  get the post thumbnail or first image as well
        $title = htmlentities(trim(strip_tags($result->post_title)), ENT_QUOTES);
        $thumb = get_the_post_thumbnail($result->ID, array(120,120), array('title'=>$title, 'alt'=>$title));
          if ( ! $thumb ) {
            $args = array(
              'numberposts'     => 1,
              'orderby'         => 'menu_order',
              'order'           => 'ASC',
              'post_type'       => 'attachment',
              'post_mime_type'  =>  'image',
              'post_parent'     => $result->ID,
            );

            $attachments =& get_posts($args);
            if ($attachments) {
              foreach($attachments as $attachment) {
                //$r->post_thumbnail = wp_get_attachment_image( $attachment->ID, 'full' );
                $result->post_thumbnail = wp_get_attachment_image( $attachment->ID, array(120,120) );
                break;
              }
            }
          }

      return $result;
  } 