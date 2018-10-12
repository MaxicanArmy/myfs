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
$owner = get_current_user_id() == $post->post_author;
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="container single" id="darwin-core">
		<?php
		if ($owner) : ?>
			<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
				<div class="row owner-actions">
					<div class="col-xs-2">
						<input type="hidden" id="dwc_specimen_id" name="dwc_specimen_id" value="<?= $post->ID ?>" />
				        <input type="hidden" name="action" value="dwc_update_specimen" />
				        <?php wp_nonce_field('dwc_update_specimen_post', 'dwc_update_specimen_nonce'); ?>
						<input class="btn btn-primary" type="submit" name="update" value="Update Specimen" />
					</div>
					<div class="col-xs-2 col-xs-offset-8">
						<?php wp_nonce_field('dwc_delete_specimen_ajax', 'dwc_delete_specimen_nonce'); ?>
						<a id="darwin-core-delete-specimen" href="#" class="btn btn-danger ajax-btn" style="float:right;">Delete Specimen</a>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6 pull-right">
						<?php 
						if (class_exists('AudubonCoreMedia')) {
							audubon_core::display_dwc_associated_media($post, $owner);
						}
						?>
					</div>
					<div class="col-xs-12 col-sm-6">
				        <div class="dwc-row">
				        	<h3>Title</h3>
				        	<input class="dwc-full-input" type="text" placeholder="Title" name="post_title" value="<?= $post->post_title ?>" />
				        </div>
				        <div class="dwc-row">
					        <h3>Description</h3>
					        <textarea class="dwc-input" name="description" rows="13"><?= get_post_meta($post->ID, 'description', true) ?></textarea>
					    </div>
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
					</div>
				</div>
				<div class="row">
					<?php $specimen->display_meta($owner); ?>
				</div>
			</form>
		<?php else : ?>
			<h1><?= $post->post_title ?></h1>
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<p><?= get_post_meta($post->ID, 'description', true) ?></p>
				</div>
				<div class="col-xs-12 col-sm-6 pull-right">
					<?php 
					if (class_exists('AudubonCoreMedia')) {
						audubon_core::display_dwc_associated_media($post, $owner);
					}
					?>
				</div>
			</div>
		<?php endif; ?>
		</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>