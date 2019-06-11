<?php
if ( !is_user_logged_in() ) {
   auth_redirect();
}
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
get_header();

$new_specimen = new DarwinCoreSpecimen();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
  <?php
    $class_tabs = array();
    $tab_content = array();
    $summary_content = "<div class='row'>";
    $hasImagesClass = "";
    $hasSummaryClass = "";
    $images = array();
		$imageSummary = "";
		$imageContent = "";

		$html = "";

    $class_tabs['Images'] = "<li class='dwc-specimen-nav-Images active".$hasImagesClass."'><a href='#' data-target='dwc-specimen-content-Images'>Images</a></li>";
    ?>
    <div id="darwin-core">
      <div id="dwc-specimen-header">
        <div class="container">
          <?php if ($new_specimen->get_user_access() != 'guest') : ?>
          <form>
            <?php wp_nonce_field('delete_dwc_specimen', 'delete_dwc_specimen_nonce'); ?>
            <a id="dwc-delete-specimen" href="#" class="btn btn-danger ajax-btn" style="float:right;clear:right;display:none;">Delete Specimen</a>
          </form>
          <?php endif; ?>
          <div><span id="dwc-specimen-header-id"></span></div>
          <div id="dwc-specimen-header-author"></div>
          <div id="dwc-specimen-header-last-updated"></div>
        </div>
      </div>
    	<div class="container single">
    <?php
		$class_layout = $new_specimen->get_layout();
		$meta_keys = $new_specimen->get_meta_keys();

    $class_layout = apply_filters('darwin-core-hide-admin-classes', $class_layout);


		$nav_counter = 1;
		$prev_prev = 'Images';

    for ($count=1;$count<=count($class_layout);$count++) {
			$summary_content .= '<div class="col-xs-12 col-sm-6">';
      ksort($class_layout[$count]);
      foreach ($class_layout[$count] as $current) {

				if ($nav_counter > 1) {
        	$tab_content[$prev]['footer'] = "<div id='dwc-footer-nav'><span class='dwc-prev dwc-specimen-nav-".$prev_prev."'><a href='#' data-target='dwc-specimen-content-".$prev_prev."'>Previous</a></span><span class='dwc-next dwc-specimen-nav-".$current."'><a href='#' data-target='dwc-specimen-content-".$current."'>Next</a></span></div></div>";
					$prev_prev = $prev;
				} else {
					$images_footer_nav = "<div id='dwc-footer-nav'><span class='dwc-next dwc-specimen-nav-".$current."'><a href='#' data-target='dwc-specimen-content-".$current."'>Next</a></span></div>";
				}
				$prev = $current;
				$nav_counter++;

        $hasContentClass = '';
        $class_output = '';
        $meta_key = $meta_keys[$current];
        $tab_content[$current]['header'] = '<div class="dwc-specimen-content-'.$current.'">'.
					'<div class="dwc-help-btn">'.do_shortcode("[sg_popup id=4 event='click']<span class='dwc-help-popup'>?</span>[/sg_popup]").
					'<div class="clear"></div></div>'.apply_filters("dwc_class_descriptions", $current).'<div class="dwc-class-header"><h3>'.
          apply_filters('darwin-core-add-'.$current.'-helper', '', $new_specimen->get_user_access()).$meta_key['displayName'].'</h3></div>';
        $summary_content .= '<div><h3>'.$meta_key['displayName'].'</h3></div>';

        foreach($meta_key['terms'] AS $termName => $termValues) {
          if (!empty($termValues['value'])) {
            $hasContentClass = ' hascontent';
            $hasSummaryClass = ' hascontent';
          }
          $tab_content[$current]['class'] = $current;
          $tab_content[$current]['terms'] .= $new_specimen->display_meta_item( $termValues['type'], $current, $termName, $termValues['displayName'], $termValues['value']);
          $summary_content .= $new_specimen->display_meta_item( $termValues['type'], $current, $termName, $termValues['displayName'], $termValues['value'], true);
        }

        $class_tabs[] = "<li class='dwc-specimen-nav-".$current.$hasContentClass."'><a href='#' data-target='dwc-specimen-content-".$current."'>".$meta_key['displayName']."</a></li>";
      }
      $summary_content .= "</div>";
    }
		$summary_content .= "</div>";
		$tab_content[$prev]['footer'] = "<div id='dwc-footer-nav'><span class='dwc-prev dwc-specimen-nav-".$prev_prev."'><a href='#' data-target='dwc-specimen-content-".$prev_prev."'>Previous</a></span><span class='dwc-next dwc-specimen-nav-Summary'><a href='#' data-target='dwc-specimen-content-Summary'>Next</a></div></div>";
    $class_tabs['Summary'] = "<li class='dwc-specimen-nav-Summary".$hasSummaryClass."'><a href='#' data-target='dwc-specimen-content-Summary'>Summary</a></li>";

    echo "<ul id='dwc-specimen-nav'>";
    foreach ($class_tabs as $tab)
      echo $tab;
    echo "</ul><div class='clear'></div>";
    ?>
	    <div class='dwc-specimen-content-Images'>
				<div class='dwc-class-description'>
		      <p>A good photo is key to a good fossil entry. There are several important aspects of taking a good photo:</p>
					<ol>
						<li data-target="plus"><a>+</a> <span>Scale</span>
              <ol>
                <li>Remember scientists use the metric system.</li>
                <li>Include the centimeters side of rulers.</li>
                <li>Scale items include coins or other things that can easily be measured by someone else.</li>
              </ol>
            </li>
						<li data-target="plus"><a>+</a> <span>Lighting</span>
              <ol>
                <li>Often it is best to take a photo outside.</li>
                <li>Make sure that there is minimal light around you.</li>
                <li>It is important that there is minimal glare on the specimen.</li>
              </ol>
            </li>
						<li data-target="plus"><a>+</a> <span>Background</span>
              <ol>
                <li>Solid color that is preferably a different color from the fossil specimen.</li>
                <li>Use a plain background, without pattern. Keep it neutral so your fossil is the star of the show.</li>
                <li>Place your fossil on a non-reflective surface to reduce glare.</li>
              </ol>
            </li>
						<li data-target="plus"><a>+</a> <span>Orientation</span>
              <ol>
                <li>Take several photos to provide detailed views of your fossil from different angles or orientations.</li>
                <li>The <a href="http://www.digitalatlasofancientlife.org/" target="_blank">Digital Atlas of Ancient Life</a> has four large initiatives and includes many different specimens with correct photographic orientations.</li>
                <li>Most smart phones are equipped with fantastic cameras, so it is okay if you don't possess a fancy digital camera.</li>
              </ol>
            </li>
					</ol>
				</div>
	      <div id='dwc-specimen-image-preview'><?php echo $imageContent; ?></div><div class="clear"></div>
				<?php if ($new_specimen->get_user_access() != 'guest') : ?>
	      <div id='dwc-dragndrop-loading'><p><img src='/wp-content/plugins/darwin-core/images/updating.gif' /> Uploading Image. Please wait...</p></div>
	      <div id='dwc-dragndrop'><p><img src='/wp-content/plugins/darwin-core/images/camera-100.png' /> Drag and Drop here to Upload Images</p></div>
	      <form>
	        <?php wp_nonce_field('dwc_specimen_upload_media', 'dwc_specimen_upload_media_nonce'); ?>
	        <input type='hidden' id='upload_media_action' name='upload_media_action' value='dwc_specimen_upload_media' />
	      </form>
				<?php endif; ?>
				<?php echo $images_footer_nav; ?>
	    </div>
			<form method="post" id="dwc-specimen-form" action="#">
	      <?php wp_nonce_field('dwc_specimen_upload_terms', 'dwc_specimen_upload_terms_nonce'); ?>
				<input type="hidden" id="dwc_specimen_id" name="dwc_specimen_id" value="" />
	      <input type="hidden" name="action" value="dwc_specimen_upload_terms" />
	      <?php
	      foreach ($tab_content as $key => $value)
	        echo $value['header'].apply_filters( 'darwin-core-alter-'.$value['class'].'-html', $value['terms'], $meta_keys[$value['class']], $new_specimen->get_user_access()).$value['footer'];
	      ?>
			</form>
	    <div class='dwc-specimen-content-Summary'>
				<div id='image-summary'>
					<?=$imageSummary?>
				</div>
				<div class='clear'></div>
				<?=$summary_content?>
				<div class='dwc-class-description'>
					<p>By clicking submit you are verifying that the information presented here is correct, this is your fossil specimen, and you collected it within the bounds of the law.  Once you click submit the fossil specimen will be sent off for curation.</p>
				</div>
				<div id="dwc-footer-nav">
					<span class='dwc-prev dwc-specimen-nav-<?=$current?>'><a href='#' data-target='dwc-specimen-content-<?=$current?>'>Previous</a></span>
					<span class='dwc-next dwc-specimen-nav-Summary'><a href='/create-specimen/' data-target='dwc-specimen-content-Summary'>Submit</a>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<script type="text/javascript">
jQuery(document).ready(function($) {
  $(".dwc-fake-header").before("<div class='dwc-class-description'><p>Lithostratigraphy is the study of rock layers.</p><ol><li data-target='plus'><a>+</a> <span>What should you include?</span><ol><li>The Group, Formation, and Member of the rock unit that you found your fossil in.</li><li>Include the Member if possible.</li><li>Apps can identify the rock through the GPS in your phone <a href='https://itunes.apple.com/us/app/mancos/id541570878?mt=8' target='_blank'>'Mancos'</a> and <a href='https://rockd.org/' target='_blank'>'Rockd'</a>.</li><li>These apps are particularly useful because they also describe what other fossils you should expect to find, a description of the rock, in some cases lat/long data, and much more.</li></div>");

  $(".dwc-class-description > ol > li").on("click", function() {
    if ($(this).attr('data-target') == 'plus') {
      $(this).attr('data-target','minus');
      $(this).children('a').html('-');
      $(this).children('ol').css('display','block');
    } else if ($(this).attr('data-target') == 'minus') {
      $(this).attr('data-target','plus');
      $(this).children('a').html('+');
      $(this).children('ol').css('display','none');
    }
  });
});
</script>
<?php
get_footer();
?>
