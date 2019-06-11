<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
get_header();

global $post;
//$post_meta = get_post_meta($post->ID);
$new_specimen = new DarwinCoreSpecimen($post->ID);
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
		$audubon_args = array(
			'post_parent' => $post->ID,
			'post_type' => 'ac_media'
		);
		$audubons = get_children($audubon_args);

		if ( count($audubons) > 0 ) {
			foreach ($audubons as $ac_media) {
				$images[$ac_media->ID] = new stdClass();
        $images[$ac_media->ID]->thumb_id = get_post_meta( $ac_media->ID, 'thumb_id', true );
    		$images[$ac_media->ID]->media_url = get_post_meta( $ac_media->ID, 'resource_url', true );
    		$images[$ac_media->ID]->media_ext = get_post_meta( $ac_media->ID, 'resource_ext', true );

        if ($images[$ac_media->ID]->media_ext == 'stl') {
          if (!empty($images[$ac_media->ID]->thumb_id)) {
						$images[$ac_media->ID]->thumb_src = wp_get_attachment_image_src( $images[$ac_media->ID]->thumb_id, 'thumbnail' )[0];
          } else {
						$images[$ac_media->ID]->thumb_src = plugin_dir_path( realpath( __FILE__ ) )."stl-no-thumb.png";
          }
        } else {
          $args = array(
            'post_parent' => $ac_media->ID,
            'post_type' => 'attachment'
          );
          $attachments = get_children($args);
          $attachment = array_shift( $attachments );
          if ( !is_null( $attachment ) ) {
						$images[$ac_media->ID]->attachment_id = $attachment->ID;
						$images[$ac_media->ID]->thumb_src = wp_get_attachment_image_src( $attachment->ID, 'thumbnail')[0];
          }
        }
			}
    }

		foreach ($images as $ac_id => $values) {
			$imageSummary .= "<div class='dwc-image-preview'><a href='".$values->media_url."' class='fbx-link fbx-instance'><img src='".$values->thumb_src."' class='wp-image-".$values->attachment_id."' /></a></div>";
			$imageContent .= "<div class='dwc-image-preview'><a href='".$values->media_url."' class='fbx-link fbx-instance'><img src='".$values->thumb_src."' class='wp-image-".$values->attachment_id."' /></a><p><a target='_blank' href='/ac-media/".$ac_id."'>View Media</a></p></div>";
		}

    if (!empty($audubons)) {
      $hasImagesClass = ' hascontent';
      $hasSummaryClass = ' hascontent';
    }

    $class_tabs['Images'] = "<li class='dwc-specimen-nav-Images".$hasImagesClass."'><a href='#' data-target='dwc-specimen-content-Images'>Images</a></li>";
    ?>
    <div id="darwin-core" class="single-dwc-specimen">
      <div id="dwc-specimen-header">
        <div class="container">
					<?php if ($new_specimen->get_grade() == "research") : ?>
					<span id="research-grade-status"><span class="research-grade-label">&#9733; RESEARCH GRADE</span></span>
					<?php endif; ?>
          <?php if ($new_specimen->get_user_access() != 'guest') : ?>
          <?php if ($new_specimen->get_user_access() == 'admin' && $new_specimen->get_curated() !== "true") : ?>
          <form>
            <?php wp_nonce_field('curate_dwc_specimen', 'curate_dwc_specimen_nonce'); ?>
            <span id="research-grade-status">
							<a id="dwc-research-grade-specimen" href="#" class="btn btn-success ajax-btn curate-specimen" data-target="research">Mark Research Grade</a>
							<a id="dwc-curated-specimen" href="#" class="btn btn-info ajax-btn curate-specimen" data-target="casual">Mark Curated</a>
							<a id="dwc-downgrade-specimen" href="#" class="btn btn-success ajax-btn curate-specimen" data-target="downgrade">Not A Specimen</a>
						</span>
          </form>
					<?php endif; ?>
					<?php if ($new_specimen->get_user_access() == 'admin' || $new_specimen->get_grade() !== "research") : ?>
          <form>
            <?php wp_nonce_field('delete_dwc_specimen', 'delete_dwc_specimen_nonce'); ?>
            <a id="dwc-delete-specimen" href="#" class="btn btn-danger ajax-btn" style="float:right;clear:right;">Delete Specimen</a>
          </form>
          <?php endif; ?>
          <?php endif; ?>
          <div><span id="dwc-specimen-header-id"><?php echo (empty($post->ID)) ? '' : 'SPECIMEN '.$post->ID; ?></span></div>
          <div id="dwc-specimen-header-author"><?php echo (empty($post->ID)) ? '' : 'Author: '.bp_core_get_user_displayname( $post->post_author ); ?></div>
          <div id="dwc-specimen-header-last-updated"><?php echo (empty($post->ID)) ? '' : 'Updated '.get_post_modified_time('m-d-Y H:i', false, $post->ID); ?></div>
        </div>
      </div>
    	<div class="container single">
    <?php
		$class_layout = $new_specimen->get_layout();
		$meta_keys = $new_specimen->get_meta_keys();

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
					'<div class="clear"></div></div><div class="dwc-class-header"><h3>'.
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
    $class_tabs['Summary'] = "<li class='dwc-specimen-nav-Summary".$hasSummaryClass." active'><a href='#' data-target='dwc-specimen-content-Summary'>Summary</a></li>";

		if ($new_specimen->get_user_access() != 'guest') :
    echo "<ul id='dwc-specimen-nav'>";
    foreach ($class_tabs as $tab)
      echo $tab;
    echo "</ul><div class='clear'></div>";
    ?>
	    <div class='dwc-specimen-content-Images'>
				<div id='dwc-specimen-image-preview'><?php echo $imageContent; ?></div><div class="clear"></div>
				<?php if ($new_specimen->get_user_access() != 'guest') : ?>
				<div id='dwc-dragndrop'><p><img src='/wp-content/plugins/darwin-core/images/camera-100.png' /> Upload Images</p></div>
				<form>
					<?php wp_nonce_field('dwc_specimen_upload_media', 'dwc_specimen_upload_media_nonce'); ?>
					<input type='hidden' id='upload_media_action' name='upload_media_action' value='dwc_specimen_upload_media' />
				</form>
			<?php endif; ?>
				<?php echo $images_footer_nav; ?>
			</div>
			<form method="post" id="dwc-specimen-form" action="#">
				<?php wp_nonce_field('dwc_specimen_upload_terms', 'dwc_specimen_upload_terms_nonce'); ?>
				<input type="hidden" id="dwc_specimen_id" name="dwc_specimen_id" value="<?=$post->ID?>" />
				<input type="hidden" name="action" value="dwc_specimen_upload_terms" />
				<?php
				foreach ($tab_content as $key => $value)
					echo $value['header'].apply_filters( 'darwin-core-alter-'.$value['class'].'-html', $value['terms'], $meta_keys[$value['class']], $new_specimen->get_user_access()).$value['footer'];
				?>
			</form>
		<?php endif; ?>
			<div class='dwc-specimen-content-Summary'>
				<div id='image-summary'>
					<?=$imageSummary?>
				</div>
				<div class='clear'></div>
				<?=$summary_content?>
				<div id="dwc-footer-nav">
					<span class='dwc-prev dwc-specimen-nav-<?=$current?>'><a href='#' data-target='dwc-specimen-content-<?=$current?>'>Previous</a></span>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div id="buddypress" class="container"><!-- display buddypress comments -->
			<?php do_action('bp_before_directory_activity_list'); ?>
			<div class="activity" role="main">
			<?php do_action( 'bp_before_activity_loop' ); ?>

			<?php if ( bp_has_activities( 'secondary_id='.$post->ID ) ) : ?>

				<?php if ( empty( $_POST['page'] ) ) : ?>

					<ul id="activity-stream" class="activity-list item-list">

				<?php endif; ?>

				<?php while ( bp_activities() ) : bp_the_activity(); ?>

					<?php bp_get_template_part( 'activity/entry' ); ?>

				<?php endwhile; ?>
			<?php endif; ?>
			</div><!-- .activity -->
			<?php do_action('bp_after_directory_activity_list'); ?>
			<?php do_action('bp_directory_activity_content'); ?>
			<?php do_action('bp_after_directory_activity_content'); ?>
			<?php do_action('bp_after_directory_activity'); ?>
		</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php
get_footer();
?>
