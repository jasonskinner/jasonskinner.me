<?php
$terms = get_terms( [ 'taxonomy' => 'work_type', ] );
?>
<ul class="dropdown menu option-set" id="work-options" data-option-key="filter" data-dropdown-menu>
	<li class="menu-text">Show me</li>
	<li><a href="#filter" data-option-value="*">All the Things</a>
		<ul class="menu">
			<li><a href="#filter" data-option-value="*">All the Things</a></li>
		<?php
			if ( !empty ( $terms ) ):
				foreach ( $terms as $term ) {
					?>
						<li><a href="#filter" data-option-value="<?php echo '.' . strtolower ( $term->name ); ?>"><?php echo $term->name; ?></a></li>
					<?php
				}
			endif;
		?>
		</ul>
	</li>
</ul>


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
	<div class="grid-x grid-padding-x grid-margin-x small-up-1 medium-up-2 large-up-2 work">
		<?php
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			?>
			<div class="cell<?php if( function_exists('jss_work_taxonomy_name')){ jss_work_taxonomy_name(); }?>">
				<a href="<?php the_permalink(); ?>">
					<div class="imghover">
						<figure>
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'work-listing', ['class' => 'work-listing-image'] );
							}
							?>
						</figure>
					</div>
					<h2 class="h3 text-center"><?php the_title(); ?></h2>
					<?php
						if ( ! empty ( $terms ) ):
							$term_result = array();
							foreach ( $terms as $term ) {
								$term_result[] = $term->name;
							}
							?>
							<div class="tags text-center">
								<?php echo implode(', ', $term_result); ?>
							</div>
						<?php
						endif;
					?>
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