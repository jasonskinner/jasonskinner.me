<?php get_header(); ?>

	<div class="content grid-container">

		<div class="inner-content grid-x">

			<main class="main large-12 cell" role="main">

				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<?php get_template_part( 'parts/about/content', 'me' ); ?>

					<?php get_template_part( 'parts/about/content', 'workplayexplore' ); ?>

					<?php get_template_part( 'parts/about/content', 'instagram' ); ?>

					<?php get_template_part( 'parts/about/content', 'resume' ); ?>

				<?php endwhile; endif; ?>

			</main> <!-- end #main -->


		</div> <!-- end #inner-content -->

	</div> <!-- end #content -->

<?php get_footer(); ?>