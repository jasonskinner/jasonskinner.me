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