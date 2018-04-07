<?php
$count = 0;
$terms = get_terms( [ 'taxonomy' => 'work_type', ] );
?>
<ul class="dropdown menu" id="work-options" data-dropdown-menu>
	<li class="menu-text">Show me</li>
	<li>
		<?php
			foreach ( $terms as $term ) {
				if ( $count == 0 ) {
					?>
					<a href="#"><?php echo $term->name; ?></a>
					<ul class="menu">
					<?php
				} else {
					?>
					<li><a href="#"><?php echo $term->name; ?></a></li>
					<?php
				}
				$count++;
			}
		?>
		</ul>
	</li4545
</ul>


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
	<div class="grid-x grid-padding-x grid-margin-x small-up-1 medium-up-2 large-up-2 work">
		<?php
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			?>
			<div class="cell<?php if( function_exists('jss_work_taxonomy_name')){ jss_work_taxonomy_name(); }?>">
				<a href="<?php the_permalink(); ?>">
					<img src="http://via.placeholder.com/650x450">
					<h2 class="text-center"><?php the_title(); ?></h2>
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