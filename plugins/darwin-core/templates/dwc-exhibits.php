<?php
get_header();
//get the list of fossils in the exhibit, then take the id of the first one and display it like on the homepage
?>
<div id="emuseum-header" class="container-fluid">
  <div class="container">
    <h3>Welcome to the</h3>
    <h1>eMuseum<span>Exhibits</span></h1>
  </div>
</div>
<div class="container">
  <div id="featured-fossil">
      <div id="featured-fossil-header">
          <h5>Improve Your Collection</h5>
          <p>Record and maintain information about your fossils. Use the myFOSSIL database to begin curating your collection. Get help with identification and classification.</p>
      </div>


      <?php
      global $post;
      $selection = get_option( 'atmo_selectors' )["fossil_id"];

      if (is_integer($selection) && $selection > 0) {
          $args = array(
              'p'             => $selection,
              'post_type'     => array( 'dwc_specimen' )
          );
      }
      else {
          $args = array(
              'posts_per_page'    => 1,
              'orderby'          => 'date',
              'order'            => 'DESC',
              'post_type'        => array( 'myfossil_fossil' ),
              'post_status'      => 'publish'
          );
      }
      // The Query
      $the_query = new WP_Query( $args );

      if ( $the_query->have_posts() ) {
          $the_query->the_post();
          $fossil = new DarwinCoreSpecimen( $post->ID );
      }

      $audubon = new AudubonCoreMedia($fossil->get_image_assets()[0]);

      $fossil_date = date( "F d, Y", strtotime( $post->post_date) );
      $string = $audubon->get_image_src();


      ?>
      <div id="featured-fossil-content" style="background-image:url('<?=$string;?>');">
          <div id="featured-fossil-details">
              <h5><a href="/dwc-specimen/<?php echo $post->ID; ?>/"><?php echo "SPECIMEN ".$post->ID; ?></a></h5>
              <p>Contributed by <?php echo bp_core_get_user_displayname( $post->post_author ); ?><br /><?php echo $fossil_date; ?></p>
          </div>
      </div>
      <?php
      /* Restore original Post Data */
      wp_reset_postdata();
      ?>
  </div>
</div>
<div class="clear"></div>
<div id="emuseum-footer" class="container-fluid">
  <div class="container">
    <p>Explore more of the <span>eMuseum</span> :</p>
    <div class="row">
      <div id="dwc-archive-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/dwc-specimen"><p>Fossil Specimens</p></a></div>
      <div id="threed-gallery-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/3d-gallery"><p>3D Specimens</p></a></div>
      <div id="aboutus-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/emuseum-aboutus"><p>About Us</p></a></div>
    </div>
  </div>
</div>
<?php
get_footer();
?>
