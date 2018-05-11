<?php
	get_header();
?>
<div class="grid-container">
	<div class="grid-x grid-padding-x">
		<div class="cell work-intro-image">
			<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'full' );
				}

			?>
		</div>

		<header class="work-header cell">
			<h1 class="entry-title single-work-title text-center" itemprop="headline"><?php the_title(); ?></h1>
		</header> <!-- end article header -->


		<div class="cell large-8">
			<h2>About the Project</h2>
			<?php
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();

					the_content();
				} // end while
			} // end if
			?>
		</div>
		<div class="cell large-4">
			<?php
				$logo = get_field('logo' );
				$size = 'full';
			?>

			<?php
			if ( !empty( $logo ) ) {
				?>
				<div class="work-single-logo">
					<img src="<?php echo $logo['url']; ?>" alt="<?php echo $logo['alt']; ?>" />
				</div>
				<?php
			}
			?>
			<hr>
			<h3>Services</h3>
			<?php

			$taxonomy = 'work_type';
			$terms = get_the_terms( get_the_ID(), $taxonomy); // Get all terms of a taxonomy

			if ( $terms && !is_wp_error( $terms ) ) :
				?>
				<ul class="menu vertical services">
					<?php foreach ( $terms as $term ) { ?>
						<li><?php echo $term->name; ?></li>
					<?php } ?>
				</ul>
			<?php endif;?>


			<?php
				$website_url = get_field( 'website_link' );
				if ( $website_url ) {
					?>
					<a target="_blank" class="button expanded" href="<?php echo $website_url; ?>">Visit Website</a>
					<?php
				}

			?>

		</div>
	</div>
</div>
<?php
	get_footer();
?>
