<div class="grid-x grid-margin-x grid-padding-x contact-available">
	<div class="large-12 cell">
		<?php
			$available = get_field ( 'contact_available' );

			if ( $available === true ) {
				?>
				<p><i class="fas fa-check-circle"></i>  I am available for freelance work. Contact me.</p>
				<?php
			}else{
				?>
				<p><i class="fas fa-times-circle"></i>  I am currently unavailable for freelance work. :(</p>
				<?php
			}
		?>

	</div><!--.cell-->
</div><!--.grid-x-->