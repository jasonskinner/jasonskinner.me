<div class="grid-x grid-margin-x grid-padding-x intro">
	<div class="large-7 cell">
		<?php the_content(); ?>
	</div><!--.cell-->
	<div class="large-5 cell contact-social">
		<ul class="menu social align-center">
			<?php
			// loop through the rows of data
			while ( have_rows( 'social_links', 11 ) ) : the_row();
				$fa   = get_sub_field( 'fontawesome_markup' );
				$text = get_sub_field( 'social_text' );
				$url  = get_sub_field( 'social_link' );
				?>
				<li>
					<a target="_blank" href="<?php echo $url; ?>">
						<?php echo $fa; ?>
						<span><?php echo $text; ?></span>
					</a>
				</li>
			<?php
			endwhile;
			?>
		</ul><!--.menu-->
	</div>
</div><!--.grid-x-->