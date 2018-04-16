<?php get_header(); ?>

	<div class="content grid-container">

		<div class="inner-content grid-x grid-padding-x grid-margin-x">

			<main class="main cell" role="main">

				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'parts/home/content', 'intro' ); ?>

					<?php get_template_part( 'parts/home/content', 'journal' ); ?>

				<?php endwhile; endif; ?>
			</main> <!-- end #main -->


		</div> <!-- end #inner-content -->

	</div> <!-- end #content -->

<?php get_footer(); ?>