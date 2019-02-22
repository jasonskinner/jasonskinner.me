<?php
/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section
 *
 */
?>

<!doctype html>
<!--
__        ___               _   _      _ _         _____ _                   _
\ \      / / |__  _   _    | | | | ___| | | ___   |_   _| |__   ___ _ __ ___| |
 \ \ /\ / /| '_ \| | | |   | |_| |/ _ \ | |/ _ \    | | | '_ \ / _ \ '__/ _ \ |
  \ V  V / | | | | |_| |_  |  _  |  __/ | | (_) |   | | | | | |  __/ | |  __/_|
   \_/\_/  |_| |_|\__, ( ) |_| |_|\___|_|_|\___/    |_| |_| |_|\___|_|  \___(_)
			      |___/|/

This site will always be changing.
If you have any questions about the code below, just ask me:  me [at] jasonskinner.me
-->
<html class="no-js"  <?php language_attributes(); ?>>

	<head>
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-38660367-1"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());

			gtag('config', 'UA-38660367-1');
		</script>


		<meta charset="utf-8">
		
		<!-- Force IE to use the latest rendering engine available -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<!-- Mobile Meta -->
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta class="foundation-mq">
		
		<!-- If Site Icon isn't set in customizer -->
		<?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) { ?>
			<!-- Icons & Favicons -->
			<link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.png">
			<link href="<?php echo get_template_directory_uri(); ?>/assets/images/apple-icon-touch.png" rel="apple-touch-icon" />	
	    <?php } ?>

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

		<?php wp_head(); ?>

	</head>
			
	<body <?php body_class(); ?>>

		<div class="off-canvas-wrapper">
			
			<!-- Load off-canvas container. Feel free to remove if not using. -->			
			<?php get_template_part( 'parts/content', 'offcanvas' ); ?>
			
			<div class="off-canvas-content" data-off-canvas-content>
				
				<header class="header" role="banner">

							    <?php get_template_part( 'parts/nav', 'offcanvas' ); ?>

				</header> <!-- end .header -->