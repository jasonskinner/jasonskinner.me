<?php
/**
 * The off-canvas menu uses the Off-Canvas Component
 *
 * For more info: http://jointswp.com/docs/off-canvas-menu/
 */
?>
<div id="sticky-container" data-sticky-container>
<div class="top-bar" data-sticky id="top-bar-menu">
	<div class="top-bar-left">
		<ul class="menu">
			<li><a href="<?php echo home_url(); ?>">
					<div class="title-bar-title">
						<?php
						get_template_part( 'parts/logo.svg' );
						?>
					</div>
				</a></li>
		</ul>
	</div>
	<div class="top-bar-right">
		<a class="curtain-menu-button" data-curtain-menu-button data-toggle="off-canvas-menu">
			<div class="curtain-menu-button-toggle">
				<div class="bar1"></div>
				<div class="bar2"></div>
			</div>
		</a>
	</div>
</div>
</div>
