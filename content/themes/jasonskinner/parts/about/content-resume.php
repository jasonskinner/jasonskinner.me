<?php
	$resume = get_field( 'resume' );

	if ( $resume ) {
		?>

		<div class="grid-x grid-margin-x grid-padding-x">
			<div class="large-12 cell">
				<div class="resume">
					<h2>Curriculum Vitae</h2>
					<a href="<?php echo $resume['url']; ?>" target="_blank" title="Download Resume"><i class="fas fa-download"></i></a>
				</div>
			</div>
		</div>

	<?php
	}
	?>
