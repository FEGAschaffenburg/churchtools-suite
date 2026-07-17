<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-wrapper">
	<div class="sticky-header-wrap">
	<header class="site-header">
		<!-- <div class="container"> -->
			<div class="hero-content">
				<div class="hero-brand">
					<div class="hero-logo" aria-hidden="true">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/cts-logo-full.svg' ); ?>" alt="" loading="lazy" width="120" height="120" />
					</div>
					<div class="hero-text">
						<div class="hero-subtitle">WordPress Integration für ChurchTools</div>
						<h1 class="hero-title">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>">ChurchTools Suite</a>
						</h1>
						<p class="hero-tagline">Professionelle Kalender-Integration • Version <?php echo esc_html( cts_demo_get_cts_version() ); ?> </p>
					</div>
					
				</div>
			</div>
		<!-- </div> -->
	</header>

	<!-- Top Navigation Bar (unter Header) -->
	<nav class="top-navigation" role="navigation" aria-label="Primary Navigation">
		<div class="container">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav-menu',
				'fallback_cb'    => 'cts_demo_fallback_menu',
				'depth'          => 3, // Erlaube 3 Menü-Ebenen (Ansichten Demo → List-Ansichten → List Modern)
			) );
			?>
		</div>
	</nav>

	<?php cts_demo_secondary_navigation(); ?>
	</div><!-- /.sticky-header-wrap -->

	<main class="site-content">
