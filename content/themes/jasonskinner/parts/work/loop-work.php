<?php
// WP_Query arguments
$args = array(
	'post_type'              => array( 'work' ),
	'posts_per_page'	=>	-1,
	'orderby'   => 'date',
	'order' => 'DESC',
);

// The Query
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {
	?>
	<div class="grid-x small-up-1 medium-up-2 large-up-4 work">
	<?php
	while ( $the_query->have_posts() ) {
		$the_query->the_post();

		$logo = get_field('logo' );
		$size = 'full';
		?>
		<div class="cell">
			<?php
				if ( !empty( $logo ) ) {
					?>
					<a href="<?php the_permalink(); ?>">
						<img src="<?php echo $logo['url']; ?>" alt="<?php echo $logo['alt']; ?>" />
					</a>
					<?php
				}

			?>

		</div>
		<?php
	}
}

wp_reset_postdata();
?>
