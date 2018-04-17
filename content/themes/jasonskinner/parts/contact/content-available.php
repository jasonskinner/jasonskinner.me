<div class="grid-x grid-margin-x grid-padding-x available">
	<div class="large-12 cell">

		<?php
			$available = get_field ( 'contact_available' );

			if ( $available === true ) {
				?>
<!--				<div class="callout available-true">-->
					<p><i class="fas fa-check-circle"></i>  Good news! I am available for freelance work.</p>
<!--				</div>-->
				<?php
			}else{
				?>
<!--				<div class="callout available-false">-->
					<p><i class="fas fa-times-circle"></i>  Sorry, I am currently unavailable for freelance work.</p>
<!--				</div>-->
				<?php
			}
		?>
	</div><!--.cell-->
</div><!--.grid-x-->