<div class="grid-x grid-margin-x grid-padding-x">
	<div class="large-12 cell">
		<h2>Instagram</h2>
	</div>
</div>


<?php
if ( function_exists( 'jss_instagram_feed' ) ) {
	$feed = jss_instagram_feed();
	if ( $feed !== false ) {
		//var_dump ( $feed );
		?>
		<div class="grid-x grid-margin-x grid-padding-x">
			<?php
			$images = $feed->data;

			foreach ( $images as $image ) {
				//var_dump( $image->images );
				$imageurl    = $image->images->standard_resolution->url;
				$imagewidth  = $image->images->standard_resolution->width;
				$imageheight = $image->images->standard_resolution->height;
				?>
				<div class="large-3 cell">
					<a href="<?php echo $image->link; ?>" target="_blank">
						<img src="<?php echo $imageurl; ?>" width="<?php echo $imagewidth; ?>"
				     height="<?php echo $imageheight; ?>"/>
					</a>
				</div>
				<?php
			}
			?>
		</div><!--.grid-x-->
		<?php
	}
}
?>