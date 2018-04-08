<?php
/**
 * The template part for displaying an author byline
 */
?>

<div class="byline">
	<p class="time"><?php the_time('M j, Y') ?></p>
	<ul class="menu simple align-center">
		<?php
		$categories = get_the_category();
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				$category_link = get_category_link( $category->cat_ID );
				?>
				<li>
					<a href="<?php echo $category_link; ?>">
						<span class="label primary"><?php echo $category->name; ?></span>
					</a>
				</li>
				<?php
			}
		}
		?>
	</ul>
</div>