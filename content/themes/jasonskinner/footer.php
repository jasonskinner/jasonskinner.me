<?php
/**
 * The template for displaying the footer. 
 *
 * Comtains closing divs for header.php.
 *
 * For more info: https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */			
 ?>
			<div class="grid-container">
				<footer class="footer" role="contentinfo">
						<div class="grid-x grid-margin-x grid-padding-x">
							<div class="small-12 medium-6 cell copyright-holder">
								<p class="source-org copyright">Copyright &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>.</p>
							</div>

							<?php
								/*
								 * Social Icons
								 */
								if ( ! is_page( 11 ) ) {
									//check if it exists
									if ( have_rows( 'social_links', 11 ) ) {
										?>
										<div class="small-12 medium-6 cell">
											<ul class="menu align-right social">
												<?php
												// loop through the rows of data
												while ( have_rows( 'social_links', 11 ) ) : the_row();
													$fa   = get_sub_field( 'fontawesome_markup' );
													$text = get_sub_field( 'social_text' );
													$url  = get_sub_field( 'social_link' );
													?>
													<li>
														<a href="<?php echo $url; ?>">
															<?php echo $fa; ?>
															<span><?php echo $text; ?></span>
														</a>
													</li>
													<?php
												endwhile;
												?>
											</ul><!--.menu-->
										</div><!--.cell-->
										<?php
									}
								}
							?>
						</div> <!-- end #inner-footer -->
				</footer> <!-- end .footer -->
			</div><!--.grid-container-->
			
			</div>  <!-- end .off-canvas-content -->
					
		</div> <!-- end .off-canvas-wrapper -->
		
		<?php wp_footer(); ?>

	</body>
	
</html> <!-- end page -->