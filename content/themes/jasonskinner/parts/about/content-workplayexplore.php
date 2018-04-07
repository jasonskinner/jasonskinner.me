<?php
if ( have_rows( 'work_play_explore' ) ): ?>
	<div class="grid-x grid-margin-x grid-padding-x text-center">
		<?php while ( have_rows( 'work_play_explore' ) ): the_row();
			$fa      = get_sub_field( 'workplayexplore_fa' );
			$content = get_sub_field( 'workplayexplore_content' );
			?>
			<div class="large-4 medium-4 cell">
				<?php
				if ( $fa ) {
					echo $fa;
				}
				if ( $content ) {
					echo $content;
				}
				?>
			</div><!--.cell-->
			<?php
		endwhile; ?>
	</div><!--.grid-x-->
<?php
endif;
?>