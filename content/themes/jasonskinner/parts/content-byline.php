<?php
/**
 * The template part for displaying an author byline
 */
?>

<div class="byline">
	<p class="time"><small><?php the_time('M j, Y') ?></small></p>
	<ul class="menu simple align-center">
		<?php
		$categories = get_the_category();
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				$category_link = get_category_link( $category->cat_ID );
				?>
				<li>
					<a href="<?php echo $category_link; ?>">
						<?php echo $category->name; ?>
					</a>
				</li>
				<?php
			}
		}
		?>
	</ul>
</div>