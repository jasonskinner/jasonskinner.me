<?php
$terms = get_terms( [ 'taxonomy' => 'work_type', ] );
?>
<ul class="dropdown menu option-set" id="work-options" data-option-key="filter" data-dropdown-menu>
	<li class="menu-text">Show me</li>
	<li><a href="#filter" data-option-value="*">All the Things</a>
		<ul class="menu">
			<li><a href="#filter" data-option-value="*">All the Things</a></li>
		<?php
			foreach ( $terms as $term ) {
				?>
					<li><a href="#filter" data-option-value="<?php echo '.' . strtolower ( $term->name ); ?>"><?php echo $term->name; ?></a></li>
				<?php
			}
		?>
		</ul>
	</li>
</ul>


<?php
// WP_Query arguments
$args = array(
	'post_type'              => array( 'work' ),
	'posts_per_page'	=>	-1,
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