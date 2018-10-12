<?php
/**
 * The header for the myFOSSIL theme.
 *
 * Displays all of the <head> section and everything up to <div id="content">
 *
 * @package myfossil
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php wp_title('|', true, 'right'); ?></title>
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
  <link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700' rel='stylesheet' type='text/css'>
  <link href='//fonts.googleapis.com/css?family=Merriweather:400,700,400italic,700italic,300,300italic' rel='stylesheet' type='text/css'>
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="page" class="hfeed site">
  <a class="skip-link screen-reader-text sr-only" href="#content">
    <?php _e('Skip to content', 'myfossil'); ?>
  </a>
<!-- outputting a bootstrapified menu the Wordpress way-->
  <nav id="header-nav" class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
      <div class="row">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">myFOSSIL Logo Home</a>
        </div>
        <div class="collapse navbar-collapse" id="navbar-collapse-1">
          <?php wp_nav_menu(array('menu' => 'myFOSSIL2016', 'container' => false, 'menu_id' => 'nav-main', 'menu_class' => 'nav navbar-nav navbar-right', 'walker' => new wp_bootstrap_navwalker())); ?>
        </div>
      </div>
    </div>
  </nav>
<!-- end bootstrapified menu -->
  <div id="content" class="site-content">
<?php 
if (is_user_logged_in()) {
  echo "<!-- testing -->";
  if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'CriOS') === false) {
    echo do_shortcode("[sg_popup id=1]");
  }
}
?>