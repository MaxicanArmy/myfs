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
$post_meta = get_post_meta($post->ID); 
$specimen = new DarwinCoreSpecimen($post->ID);
$owner = ( get_current_user_id() == $post->post_author || current_user_can('administrator') );
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="container single" id="darwin-core">
			<form method="post" id="dwc-specimen-update-form" action="#">
				<?php if ( $owner ) : ?>
				<div class="row owner-actions">
					<div class="col-xs-6">
						<input type="hidden" id="dwc_specimen_id" name="dwc_specimen_id" value="<?= $post->ID ?>" />
				        <input type="hidden" name="action" value="update_dwc_specimen" />
				        <?php wp_nonce_field('update_dwc_specimen', 'update_dwc_specimen_nonce'); ?>
						<a id="darwin-core-update-specimen" href="#" class="btn btn-primary ajax-btn">Update Specimen</a><span id="darwin-core-update-msg"></span>
					</div>
					<div class="col-xs-2 col-xs-offset-4">
						<?php wp_nonce_field('delete_dwc_specimen', 'delete_dwc_specimen_nonce'); ?>
						<a id="darwin-core-delete-specimen" href="#" class="btn btn-danger ajax-btn" style="float:right;">Delete Specimen</a>
					</div>
				</div>
			<?php endif; ?>
				<div class="row">
					<div class="col-xs-12 col-sm-6 pull-right">
						<?php 
						if (class_exists('AudubonCoreMedia')) {
							audubon_core::display_dwc_associated_media($post, $owner);
						}
						?>
						<?php
						if ($owner) : ?>
						<div class="ac-create-btn-wrapper">
							<?php wp_nonce_field('upload_media_for_dwc_specimen', 'upload_media_for_dwc_specimen_nonce'); ?>
							<input type="hidden" id="upload_media_action" name="upload_media_action" value="upload_media_for_dwc_specimen" />
							<div id="dwc-dragndrop"><p>Drag &amp; Drop Media Here</p></div>
							<div>
								<?php wp_nonce_field('upload_media_url_for_dwc_specimen', 'upload_media_url_for_dwc_specimen_nonce'); ?>
						        <input id="dwc-media-url" class="dwc-input" type="text" value="" placeholder="Or enter URL" name="dwc-media_url" />
								<a id="dwc-upload-media-url" href="#" class="btn btn-primary ajax-btn">Save URL &rarr;</a>
							</div>
						</div>
					<?php endif; ?>
					</div>
					<div class="col-xs-12 col-sm-6">
				        <div class="dwc-row">
				        	<h3>Title</h3>
				        	<input class="dwc-full-input" type="text" placeholder="Title" name="post_title" value="<?= $post->post_title ?>"<?= ( ( !$owner ) ? ' disabled' : '' ) ?> />
				        </div>
				        <div class="dwc-row">
					        <h3>Description</h3>
					        <textarea class="dwc-full-input" name="description" rows="<?= ( $owner ) ? 13 : 7; ?>"  <?= ( ( !$owner ) ? ' disabled' : '' ) ?>><?= get_post_meta($post->ID, 'description', true) ?></textarea>
					    </div>
					    <?php if ( $owner ) : ?>
				        <div class="dwc-row">
					        <?php $draft = ($post->post_status === 'draft') ? true : false; ?>
					        <label class="btn btn-default <?php echo ( $draft ) ? "btn-active active" : null ?>">
					            <input type="radio" name="post_status" id="draft" autcomplete="off" class="post_status" value="draft" <?= ($draft) ? "checked " : "" ?>/>
					            <i class="fa fa-fw fa-eye-slash"></i>
					            Private
					        </label>
					        <label class="btn btn-default <?php echo ( $draft ) ? null : "btn-active active" ?>">
					            <input type="radio" name="post_status" id="publish" autcomplete="off" class="post_status" value="publish" <?= (!$draft) ? "checked " : "" ?>/>
					            <i class="fa fa-fw fa-eye"></i>
					            Public
					        </label>
					    </div>
					<?php endif; ?>
					</div>
				</div>
				<div class="row">
					<?php $specimen->display_meta($owner); ?>
				</div>
			</form>
			<?php comments_template(); ?>
		</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>