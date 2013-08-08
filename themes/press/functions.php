<?php

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
