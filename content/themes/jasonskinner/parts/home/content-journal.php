<div class="grid-x grid-padding-x">
	<div class="large-12 cell link-to-side">
		<h2>Latest Journal Posts</h2>
		<a href="<?php the_permalink('5'); ?>">(See all Journal Entries)</a>
	</div><!--.cell-->
</div><!--.grid-x-->

<?php
$args = array(
	'post_type' => array('post'),
	'post_status' => 'publish',
);

// The Query
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {
	?>
	<div class="grid-x grid-padding-x small-up-1 medium-up-3 large-up-3">
		<?php
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			?>
			<div class="cell">
				<a href="<?php the_permalink(); ?>">
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'journal-listing' );
					}
					?>
					<div class="blackbox">
						<h6>
							<?php
							$categories = get_the_category();
							if ( ! empty( $categories ) ) {
								echo esc_html( $categories[0]->name );
							}
							?>
						</h6>
						<h3><?php the_title(); ?></h3>
						<p><?php the_time('F j, Y') ?></p>
						<p><?php the_excerpt(); ?>
					</div>
				</a>
			</div><!--.cell-->
			<?php
		}
		?>
	</div><!--.grid-x-->
	<?php
	/* Restore original Post Data */
	wp_reset_postdata();
}
?>