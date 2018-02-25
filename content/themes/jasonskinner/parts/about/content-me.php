<div class="grid-x grid-margin-x grid-padding-x">
	<div class="large-12 cell text-center">
		<?php
			$image = get_field( 'personal_photo' );

			if ( ! empty ( $image ) ): ?>
				<img class="personalphoto" src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
			<?php endif; ?>
	</div><!--.cell-->
</div><!--.grid-x-->

<div class="grid-x grid-margin-x grid-padding-x">
	<div class="large-6 large-offset-3 cell text-center">
		<?php the_content(); ?>
		<?php
		/*
		 * Social Icons
		 */

		//check if it exists
		if ( have_rows ( 'social_links', 11 ) ) {
			?>
			<div class="small-12 medium-6 cell">
				<ul class="menu align-center social">
					<?php
					// loop through the rows of data
					while ( have_rows('social_links', 11) ) : the_row();
						$fa = get_sub_field( 'fontawesome_markup' );
						$text = get_sub_field( 'social_text' );
						$url = get_sub_field( 'social_link' );
						?>
						<li>
							<a href="<?php echo $url; ?>">
								<?php echo $fa; ?>
								<span><?php echo $text; ?></span>
							</a>
						</li>
						<?php
					endwhile;
					?>
				</ul><!--.menu-->
			</div><!--.cell-->
			<?php
		}
		?>
		<a href="<?php the_permalink(11); ?>" class="button primary"><i class="far fa-envelope"></i> Message Me</a>
	</div><!--.cell-->
</div><!--.grid-x-->