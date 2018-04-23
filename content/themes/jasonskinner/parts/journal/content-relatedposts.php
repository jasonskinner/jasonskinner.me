<?php
$args = array(
	'posts_per_page' => 2, //we only want this number
	'post__not_in'   => array( get_the_ID() ), //don't include current post
	'not_found_rows' => true //no pagination
);

//check for current post category object
$cats = wp_get_post_terms( get_the_ID(), 'category' );
//setup empty array
$cats_ids = array();

//loop through
foreach ( $cats as $cat ) {
	$cats_ids[] = $cat->term_id;
}

//check if empty
if ( ! empty ( $cats_ids ) ) {
	$args['category__in'] = $cats_ids;
}

//setup query
$related_query = new WP_Query( $args );

if ( $related_query->have_posts() ):
?>


<div class="journal-related">
	<hr>
	<div class="link-to-side">
		<h3>Keep Reading</h3>
		<a href="#">View all journal posts</a>
	</div>

	<div class="grid-x grid-margin-x small-up-1 medium-up-2 large-up-2">

		<?php

		//loop
		foreach ( $related_query->posts as $post ) : setup_postdata( $post );
			?>


			<?php get_template_part( 'parts/journal/loop', 'journal' ); ?>


		<?php
		endforeach;
		endif;
		?>
	</div><!--.grid-x-->
</div><!--.journal-related-->
<?php

//reset
wp_reset_postdata();
