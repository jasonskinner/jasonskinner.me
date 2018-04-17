<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 */

get_header(); ?>

	<div class="content grid-container">

		<div class="inner-content grid-x grid-margin-x grid-padding-x">

		    <main class="main small-12 medium-12 large-12 cell" role="main">

			    <?php if (have_posts()) : ?>
			        <div class="grid-x grid-padding-x grid-margin-x small-up-1 medium-up-2 large-up-2">

					    <?php while (have_posts()) : the_post(); ?>

					    <?php get_template_part( 'parts/journal/loop', 'journal' ); ?>

						<?php endwhile; ?>

			        </div><!--.grid-x-->
				<?php endif; ?>

		    </main> <!-- end #main -->

		</div> <!-- end #inner-content -->

	</div> <!-- end #content -->

<?php get_footer(); ?>