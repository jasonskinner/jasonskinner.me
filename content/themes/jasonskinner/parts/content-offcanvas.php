<?php
/**
 * The template part for displaying offcanvas content
 *
 * For more info: http://jointswp.com/docs/off-canvas-menu/
 */
?>

<div class="off-canvas position-top" id="off-canvas-menu" data-off-canvas data-transition="overlap">
	<div class="grid-container">
		<div class="grid-x grid-padding-x grid-margin-x">
<!--			<button class="nav-close" data-close="off-canvas-menu"><i class="fas fa-times"></i></button>-->
			<div class="cell medium-4 off-canvas-social">
				<h6>Social</h6>
				<ul class="menu vertical">
					<?php
					if ( have_rows( 'social_links', 11 ) ) {
						while ( have_rows( 'social_links', 11 ) ) : the_row();
							$text = get_sub_field( 'social_text' );
							$url  = get_sub_field( 'social_link' );
							?>
							<li>
								<a target="_blank" href="<?php echo $url; ?>">
									<?php echo $text; ?>
								</a>
							</li>
						<?php
						endwhile;
					}
					?>
				</ul>
			</div>
			<div class="cell medium-4 off-canvas-nav">
				<h6>Contents</h6>
				<?php joints_off_canvas_nav(); ?>
			</div>
			<div class="cell medium-4 off-canvas-journal">
				<h6>Journal</h6>
				<ul class="menu">
					<?php
						$categories = get_categories( array(
							'orderby' => 'name',
							'order'   => 'ASC'
						) );
						//var_dump( $categories );
						foreach ( $categories as $category ) {
							?>
							<li>
								<a href="<?php echo get_category_link( $category->term_id ); ?>">
									<?php echo $category->name; ?>
								</a>
							</li>
							<?php
						}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>
