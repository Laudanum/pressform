<?php
/*
Plugin Name: Posts in category menu
Plugin URI: http://houseoflaudanum.com/wordpress/posts-in-category-menu/
Description: Add sub menu items for all posts in a category menu item.
Version: 0.3
Author: Laudanum
Author URI: http://houseoflaudanum.com/identities/mr-snow/
License: GPL2
*/

/*  Copyright 2012  Laudanum  (email : mr.snow@houseoflaudanum.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


  /* add a filter to menus to add posts under categories */
  if ( ! is_admin() )
    add_filter('wp_nav_menu_objects', 'hol_nav_menu_objects', 10, 2);
  else
    hol_hide_meta_in_nav_menu();
//  add_filter('nav_menu_meta_box_object', 'hol_nav_menu_meta_box_object', 10, 2);


  function _filter_taxonomy($post_type) {
    $taxonomies = array('category');
    return ( 0 == count(array_diff( $taxonomies, get_object_taxonomies($post_type) ) ) );
  }


  /* 
    Detect menu_items that are type_label==Category and add posts in that 
    category to menu_items as children of that menu_item 
  */
  function hol_nav_menu_objects($sorted_menu_items, $args) {
//  get all post types that support the taxonomy 'category'
    $post_types = array_filter(
      get_post_types(array(), 'names'), '_filter_taxonomy'
    );
//  flatten the array
    $post_types = array_values($post_types);

    foreach ($sorted_menu_items as $menu_item) {
      if ( $menu_item->type == 'taxonomy' && $menu_item->object == "category" ) {
        $menu_item_parent = $menu_item->ID;
        $term_id = $menu_item->object_id;
//  go and get all posts in this category
//  @todo abstract out post_type to use all post_types that support this taxonomy type
//  @TODO add child categories into the list (currently we add them manually)
        $args = array(
          "orderby" => "post_title",
          "order" => "ASC",
          "post_type" => $post_types,
          "numberposts" => 100,
          "posts_per_page" => 100,
          'tax_query' => array(
            array(
              'taxonomy' => "category",
              'field' => 'id',
              'terms' => $term_id, // Where term_id of Term 1 is "1".
              'operator' => "IN",
              'include_children'=>0,
            ),
          ),
        );
        $query = new WP_Query( $args );
        $children = $query->posts;

        $i = 0;
        global $post;
        foreach ( $children as $child ) {
          $current = 0;
          if ( $post->ID == $child->ID ) {
            $current = 1;
          }
/*
        print "parent: " . $menu_item_parent . 
        ", term: " . $term_id .
        ", child: " . $child->ID .
        "<br>";
*/
//          $child->ID = count($sorted_menu_items);
          $child->menu_item_parent = $menu_item_parent;
          $child->post_type = 'nav_menu_item';
          //$child->url = $child->guid;
          $child->url = get_permalink($child->ID);
          $child->title = $child->post_title;
          $child->menu_order = $i++;
          $child->object = 'post';
          $child->type = 'post_type';
          $child->type_label = 'Post';
          $child->target = '';
          $child->attr_title = '';
          $child->description = '';
          $child->xfn = '';
          $child->current = $current;
/*
  we are missing these attributes
  object_id
  current 0:1
  current_item_ancestor ''
  current_item_parent ''
*/
          $child->classes = array(
            'menu-item',
            'menu-item',
            'menu-item-type-post_type',
          );
//  check for current-menu-item and current_post_item            
          if ( $current ) {
            $child->classes[] = "current-menu-item";
            $child->classes[] = "current_post_item";
          }
          // the problem is that items end up in their parents and grandparents.
          // ignore grandparents if there is an appropriate parent
          array_push($sorted_menu_items, $child);
        }
      }
    }
    //print_r($sorted_menu_items);
    return $sorted_menu_items;
  }
  
  
  /*
    Create a new meta box to add categories instead of the WP default.
    http://wordpress.stackexchange.com/questions/4782/overwriting-core-wordpress-functions-with-plugins
  */
  function hol_nav_menu_meta_box_object($post_type) {
    print "<pre>";
    print_r($post_type);
    print "</pre>";
    return $post_type;
  }
  
  
 /*
  function wp_nav_menu_post_type_meta_boxes() {
        $post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'object' );

        if ( ! $post_types )
            return;

        foreach ( $post_types as $post_type ) {
            $post_type = apply_filters( 'nav_menu_meta_box_object', $post_type );
            if ( $post_type ) {
                $id = $post_type->name;
                add_meta_box( "add-{$id}", $post_type->labels->name, 'wp_nav_menu_item_post_type_meta_box', 'nav-menus', 'side', 'default', $post_type );
            }
        }
  }
  */
  
  
  /* Remove the builtin WP add categories metabox */
  function hol_hide_meta_in_nav_menu() {
    if ( function_exists('remove_meta_box') ) {
//        remove_meta_box('add-custom-links', 'nav-menus', 'side');
//        remove_meta_box('nav-menu-theme-locations', 'nav-menus', 'side');
        remove_meta_box('add-category', 'nav-menus', 'side');
      }
  }

?>