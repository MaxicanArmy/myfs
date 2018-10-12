<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package myfossil2017
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'myfossil2017' ); ?></a>

	<header id="masthead" class="site-header" role="banner">
		<nav id="header-nav" class="navbar navbar-default navbar-fixed-top" role="navigation">
		    <div class="container">
		    	<div class="navbar-header">
		        	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
		            	<span class="sr-only">Toggle navigation</span>
		            	<span class="icon-bar"></span>
		            	<span class="icon-bar"></span>
		            	<span class="icon-bar"></span>
		        	</button>
		        	<a class="navbar-brand" href="/" title="myFOSSIL Logo Home"></a>
		      	</div>
		      	<div class="collapse navbar-collapse" id="navbar-collapse-1">
		        	<?php wp_nav_menu(array('menu' => 'myFOSSIL2016', 'container' => false, 'menu_id' => 'nav-main', 'menu_class' => 'nav navbar-nav navbar-right', 'walker' => new wp_bootstrap_navwalker())); ?>
		      	</div>
		    </div>
	  	</nav>
	</header><!-- #masthead -->
	<div id="content" class="site-content">
