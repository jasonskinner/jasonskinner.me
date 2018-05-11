<?php
/**
 * The off-canvas menu uses the Off-Canvas Component
 *
 * For more info: http://jointswp.com/docs/off-canvas-menu/
 */
?>
<div data-sticky-container>
<div class="top-bar" data-sticky data-options="marginTop:0;" style="margin-right: 1px;" id="top-bar-menu">
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
		<ul class="menu">
			<!-- <li><button class="menu-icon" type="button" data-toggle="off-canvas"></button></li> -->
			<li>
				<a data-toggle="off-canvas-menu">
					<button class="menu-icon" type="button" data-toggle></button>
				</a>
			</li>
		</ul>
	</div>
</div>
</div>