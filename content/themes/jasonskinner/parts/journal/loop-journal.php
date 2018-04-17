<div class="cell journal-post">
	<a href="<?php the_permalink(); ?>" class="journal-image imghover">
		<figure>
			<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'journal-listing', ['class' => 'journal-listing-image'] );
				}
			?>
		</figure>
	</a>
		<div class="summary">
			<p class="time"><small><?php the_time('M j, Y') ?></small></p>
			<ul class="menu simple">
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
			<h2 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<p><?php the_excerpt(); ?>
			<a class="read-more" href="<?php the_permalink(); ?>"><i class="fas fa-angle-right"></i> Read More</a>
		</div>
	</a>
</div><!--.cell-->