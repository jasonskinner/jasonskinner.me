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
			<p class="time"><?php the_time('M j, Y') ?></p>
			<ul class="menu simple">
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

			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<p><?php the_excerpt(); ?>
			<a href="<?php the_permalink(); ?>">Read More</a>
		</div>
	</a>
</div><!--.cell-->