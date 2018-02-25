<?php get_header(); ?>

	<div class="content grid-container">

		<div class="inner-content grid-x">

			<main class="main large-12 cell" role="main">

				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php get_template_part( 'parts/contact/content', 'intro' ); ?>
					<?php get_template_part( 'parts/contact/content', 'available' ); ?>
					<?php get_template_part( 'parts/contact/content', 'form' ); ?>

				<?php endwhile; endif; ?>

			</main> <!-- end #main -->


		</div> <!-- end #inner-content -->

	</div> <!-- end #content -->

<?php get_footer(); ?>