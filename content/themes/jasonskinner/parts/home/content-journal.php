<div class="grid-x grid-padding-x grid-margin-x small-up-1 medium-up-2 large-up-2">
	<?php
	// WP_Query arguments
	$args = array(
		'post_type'      => array( 'post' ),
		'post_status'    => array( 'publish' ),
		'posts_per_page' => '2',
		'order'          => 'DESC',
		'orderby'        => 'date',
	);

	// The Query
	$jounral_query = new WP_Query( $args );

	// The Loop
	if ( $jounral_query->have_posts() ) {
		while ( $jounral_query->have_posts() ) {
			$jounral_query->the_post();
			get_template_part( 'parts/journal/loop', 'journal' );
		}
	} else {
		// no posts found
	}

	// Restore original Post Data
	wp_reset_postdata();

	?>
</div>
