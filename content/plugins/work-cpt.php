<?php
/*
Plugin Name: jasonskinner.me work CPT
Plugin URI: http://jasonskinner.me/
Description: Adds work custom post type to admin.
Version: 1.0
Author: Jason Skinner
*/

// let's create the function for the custom type
function work_cpt() {
	// creating (registering) the custom type
	register_post_type( 'work', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array('labels' => array(
			'name' => __('Work', 'jointswp'), /* This is the Title of the Group */
			'singular_name' => __('Work', 'jointswp'), /* This is the individual type */
			'all_items' => __('All Work', 'jointswp'), /* the all items menu item */
			'add_new' => __('Add New', 'jointswp'), /* The add new menu item */
			'add_new_item' => __('Add New Work', 'jointswp'), /* Add New Display Title */
			'edit' => __( 'Edit', 'jointswp' ), /* Edit Dialog */
			'edit_item' => __('Edit Work', 'jointswp'), /* Edit Display Title */
			'new_item' => __('New Work', 'jointswp'), /* New Display Title */
			'view_item' => __('View Work', 'jointswp'), /* View Display Title */
			'search_items' => __('Search Work', 'jointswp'), /* Search Custom Type Title */
			'not_found' =>  __('Nothing found in the Database.', 'jointswp'), /* This displays if there are no entries yet */
			'not_found_in_trash' => __('Nothing found in Trash', 'jointswp'), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
		), /* end of arrays */
		      'description' => __( 'jasonskinner.me work custom post type', 'jointswp' ), /* Custom Type Description */
		      'public' => true,
		      'publicly_queryable' => true,
		      'exclude_from_search' => false,
		      'show_ui' => true,
		      'query_var' => true,
		      'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */
		      'menu_icon' => 'dashicons-portfolio', /* the icon for the custom post type menu. uses built-in dashicons (CSS class name) */
		      'rewrite'	=> array( 'slug' => 'work', 'with_front' => true ), /* you can specify its url slug */
		      'has_archive' => false, /* you can rename the slug here */
		      'capability_type' => 'post',
		      'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			  'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'page-attributes')
		) /* end of options */
	); /* end of register post type */

	/* this adds your post categories to your custom post type */
	//register_taxonomy_for_object_type('category', 'work');
	/* this adds your post tags to your custom post type */
	//register_taxonomy_for_object_type('post_tag', 'locations');

}

// adding the function to the Wordpress init
add_action( 'init', 'work_cpt');

/*
for more information on taxonomies, go here:
http://codex.wordpress.org/Function_Reference/register_taxonomy
*/

// now let's add custom categories (these act like categories)
register_taxonomy( 'work_type',
	array('work'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
	array('hierarchical' => true,     /* if this is true, it acts like categories */
	      'labels' => array(
		      'name' => __( 'Work Type', 'jointswp' ), /* name of the custom taxonomy */
		      'singular_name' => __( 'Work Type', 'jointswp' ), /* single taxonomy name */
		      'search_items' =>  __( 'Search Work Type', 'jointswp' ), /* search title for taxomony */
		      'all_items' => __( 'All Work Types', 'jointswp' ), /* all title for taxonomies */
		      'parent_item' => __( 'Parent Work Type', 'jointswp' ), /* parent title for taxonomy */
		      'parent_item_colon' => __( 'Parent Work Type:', 'jointswp' ), /* parent taxonomy title */
		      'edit_item' => __( 'Edit Work Type', 'jointswp' ), /* edit custom taxonomy title */
		      'update_item' => __( 'Update Work Type', 'jointswp' ), /* update title for taxonomy */
		      'add_new_item' => __( 'Add New Work Type', 'jointswp' ), /* add new title for taxonomy */
		      'new_item_name' => __( 'New Custom Work Type', 'jointswp' ) /* name title for taxonomy */
	      ),
	      'show_admin_column' => true,
	      'show_ui' => true,
	      'query_var' => true,
	      'rewrite' => false
	)
);
//
// now let's add custom tags (these act like categories)
//register_taxonomy( 'custom_tag',
//	array('custom_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
//	array('hierarchical' => false,    /* if this is false, it acts like tags */
//	      'labels' => array(
//		      'name' => __( 'Custom Tags', 'jointswp' ), /* name of the custom taxonomy */
//		      'singular_name' => __( 'Custom Tag', 'jointswp' ), /* single taxonomy name */
//		      'search_items' =>  __( 'Search Custom Tags', 'jointswp' ), /* search title for taxomony */
//		      'all_items' => __( 'All Custom Tags', 'jointswp' ), /* all title for taxonomies */
//		      'parent_item' => __( 'Parent Custom Tag', 'jointswp' ), /* parent title for taxonomy */
//		      'parent_item_colon' => __( 'Parent Custom Tag:', 'jointswp' ), /* parent taxonomy title */
//		      'edit_item' => __( 'Edit Custom Tag', 'jointswp' ), /* edit custom taxonomy title */
//		      'update_item' => __( 'Update Custom Tag', 'jointswp' ), /* update title for taxonomy */
//		      'add_new_item' => __( 'Add New Custom Tag', 'jointswp' ), /* add new title for taxonomy */
//		      'new_item_name' => __( 'New Custom Tag Name', 'jointswp' ) /* name title for taxonomy */
//	      ),
//	      'show_admin_column' => true,
//	      'show_ui' => true,
//	      'query_var' => true,
//	)
//);

/*
	looking for custom meta boxes?
	check out this fantastic tool:
	https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
*/