<?php
// WP_Query arguments
$args = array(
	'post_type'              => array( 'work' ),
);

// The Query
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {
	?>
	<div class="grid-x grid-padding-x small-up-1 medium-up-3 large-up-3 work">
		<?php
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			?>
			<div class="cell">
				<a href="<?php the_permalink(); ?>">
					<div class="hover">
						<?php
							$background_image = get_field( 'work_listing_background' );
							if ( !empty ( $background_image ) ){
								$background_render = 'style="background-image: url(' . $background_image['sizes']['journal-listing'] . ');"';
							} else {
								$background_render = 'style="background-color: #292929;"';
							}
						?>

						<div class="workbox" <?php echo $background_render; ?>>
							<?php
							//get category
							$terms = get_the_term_list( $post->ID, 'work_type', '', ',  ');
							$terms = strip_tags( $terms );
							?>
							<?php echo $terms; ?>
							<?php

							//get logo
							$logo = get_field( 'work_logo' );
							$size = 'work-logo';

							if( !empty($logo) ) {
								?>
								<div class="logo">
									<img src="<?php echo $logo['url']; ?>" alt="<?php echo $logo['alt']; ?>" />
								</div>
								<?php
							}
							?>

						</div>
						<div class="overlay">
							<?php the_title(); ?>
						</div>

					</div>
				</a>
			</div><!--.cell-->
			<?php
		}
		?>
	</div><!--grid-x-->
	<?php
	/* Restore original Post Data */
	wp_reset_postdata();
} else {
	// no posts found
}