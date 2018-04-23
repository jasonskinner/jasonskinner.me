<?php
function site_scripts() {
  global $wp_styles; // Call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way
        
    // Adding scripts file in the footer
    wp_enqueue_script( 'site-js', get_template_directory_uri() . '/assets/scripts/scripts.js', array( 'jquery' ), get_template_directory() . '/assets/scripts/js', true );

    // Register main stylesheet
    wp_enqueue_style( 'site-css', get_template_directory_uri() . '/assets/styles/style.css', array(), filemtime(get_template_directory() . '/assets/styles/scss'), 'all' );

    // typekit
	//wp_enqueue_script( 'typekit', '//use.typekit.net/lxx0qmt.css', array(), '1.0.0' );

	// google font api
	wp_enqueue_style( 'google-font-api', 'https://fonts.googleapis.com/css?family=Lora|Open+Sans:400,400i,700,700i', false );


	//typed
	if ( is_front_page() ){
		wp_enqueue_script( 'type-js', get_template_directory_uri() . '/assets/scripts/typed.min.js' , array( 'site-js' ), '2.0.6', true );
	}

	//waypoint
	//wp_enqueue_script( 'waypoint-js', get_template_directory_uri() . '/assets/scripts/waypoint.min.js' , array( 'site-js' ), '2.0.6', true );

	//isotope
	if ( is_page( 'work' ) ) {
		wp_enqueue_script( 'imagesloaded-js', get_template_directory_uri() . '/assets/scripts/imagesloaded.min.js', array( 'isotope-init-js' ), '4.1.4', true );
		wp_enqueue_script( 'isotope-init-js', get_template_directory_uri() . '/assets/scripts/init-isotope.js', array( 'isotope-js' ), filemtime(get_template_directory() . '/assets/scripts/init-isotope.js'), true );
		wp_enqueue_script( 'isotope-js', get_template_directory_uri() . '/assets/scripts/isotope.min.js' , array( 'site-js' ), '3.0.5', true );
	}

	//font-awesome
	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/assets/styles/font-awesome/fontawesome.css', array(), '5.0.6' );
	wp_enqueue_style( 'font-awesome-solid', get_template_directory_uri() . '/assets/styles/font-awesome/fa-solid.css', array(), '5.0.6' );
	wp_enqueue_style( 'font-awesome-brands', get_template_directory_uri() . '/assets/styles/font-awesome/fa-brands.css', array(), '5.0.6' );
	wp_enqueue_style( 'font-awesome-regular', get_template_directory_uri() . '/assets/styles/font-awesome/fa-regular.css', array(), '5.0.6' );

    // Comment reply script for threaded comments
    if ( is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
      wp_enqueue_script( 'comment-reply' );
    }
}
add_action('wp_enqueue_scripts', 'site_scripts', 999);
