<?php
/**
 * Displays archive pages if a speicifc template is not set. 
 *
 * For more info: https://developer.wordpress.org/themes/basics/template-hierarchy/
 */

get_header(); ?>
			
	<div class="content grid-container">
	
		<div class="inner-content grid-x grid-margin-x grid-padding-x">
		
		    <main class="main small-12 cell" role="main">
			    <div class="grid-x grid-padding-x grid-margin-x">
			        <header class="cell">
			            <h1 class="page-title"><?php the_archive_title();?></h1>
						<?php the_archive_description('<div class="taxonomy-description">', '</div>');?>
			        </header>
			    </div>
			    <div class="grid-x grid-padding-x grid-margin-x small-up-1 medium-up-2 large-up-2">
			        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

						<!-- To see additional archive styles, visit the /parts directory -->
						<?php get_template_part( 'parts/loop', 'archive-grid' ); ?>

					<?php endwhile; ?>

						<?php joints_page_navi(); ?>

					<?php else : ?>

						<?php get_template_part( 'parts/content', 'missing' ); ?>

					<?php endif; ?>
			    </div>
			</main> <!-- end #main -->

	    
	    </div> <!-- end #inner-content -->
	    
	</div> <!-- end #content -->

<?php get_footer(); ?>