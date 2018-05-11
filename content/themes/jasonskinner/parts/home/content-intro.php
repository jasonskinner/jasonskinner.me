<div class="large-12 cell home-intro">
	<div class="grid-x grid-padding-x grid-margin-x">
		<div class="large-8 cell">
			<h1>
				I am a developer & designer. I revel in <br>
			<span id="typed-container">
				<div id="typed-strings">
					<p>code.^2000</p>
					<p>pixels.^2000</p>
					<p>guitar.^2000</p>
					<p>watches.^2000</p>
					<p>games.^2000</p>
					<p>technology.^2000</p>
					<p>dogs.^2000</p>
				</div>
				<span id="typed"></span>
			</span>
			</h1>
			<?php
			global $post;
			$content = $post->post_content;

			if ( !empty( $content ) ) :
				echo $content;
			endif;
			?>
		</div><!--.cell-->
	</div><!--.grid-x-->
</div>