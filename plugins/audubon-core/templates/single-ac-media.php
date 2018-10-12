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
//$post_meta = get_post_meta( $post->ID );
$ac_media = new AudubonCoreMedia( $post->ID );
$owner = (get_current_user_id() == $post->post_author || is_super_admin());
$media_url = get_post_meta( $post->ID, 'resource_url', true );
$media_type = get_post_meta( $post->ID, 'resource_ext', true );
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div id="audubon-core">
	<?php if ($owner) : ?>
		<form id="ac-media-update-form" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="update_ac_media" />
			<!--<input type="hidden" name="ac_media_action" id="ac_media_action" value="ac_media_upload_wizard" />-->
			<input type="hidden" id="ac_media_id" name="ac_media_id" value="<?= $post->ID ?>" />
   	<?php endif; ?>
			<div class="ac-single-header">
				<div class="container">
					<div class="row">
						<div class="col-xs-6">
							<?php if ($owner) : ?>
							        <div class="ac-row">
							        	<input class="ac-full-input ac-h2-input" type="text" placeholder="Title" name="post_title" value="<?= $post->post_title ?>" />
							        </div>
							<?php else : ?>
					   			<h2><?= $post->post_title ?></h2>
						   	<?php endif; ?>
						</div>
						<div class="col-xs-6">
							<?php echo "<a class='btn btn-primary ac-dl-media' href='$media_url' download>Download Media</a>"; ?>
						</div>
					</div>
				<?php if ($post->post_parent > 0) : ?>
					<div class="row">
						<div class="col-xs-12">
							<!--<p><a href="/dwc_specimen/<?= $post->post_parent ?>/">&larr;&nbsp;Specimen</a></p>-->
						</div>
					</div>
				<?php endif; ?>
					<ul class="ac-media-nav">
						<li class="ac-media-nav-tab">INFORMATION</li>
					</ul>
			   	</div>
			</div><!-- .ac-single-header -->
			<div class="container ac-media-content">
				<div class="row">
					<div class="col-xs-12 col-md-6 pull-right">
						<div class="ac-object-box">
							<?php
							audubon_core::display_ac_media("main", $post, '506');
							?>
						</div>
						<?php if ( $media_type == 'stl' ) : ?>
						<p class="help-info">To zoom in and out use the scroll wheel on your mouse while hovering over the 3D image. Hold left click on the 3D image and drag the mouse to rotate.</p>
					<?php endif; ?>
					</div>
					<div class="col-xs-12 col-md-6">
					<?php if ($owner) : ?>
				        <div class="ac-row">
			   			<h3 class="ac-meta-heading">Description</h3>
				        	<textarea class="ac-full-input" placeholder="Description" name="description" rows="11"><?= get_post_meta($post->ID, 'description', true) ?></textarea>
				        </div>
						<div class="ac-row">
							<?php wp_nonce_field('upload_ac_media_thumb', 'upload_ac_media_thumb_nonce'); ?>
							<div id="ac-dragndrop-thumb"><p>Drag &amp; Drop thumbnail (.png, .jpeg, .gif) here. This will replace the current thumnail.</p></div>
						</div>
				    <?php else : ?>
			   			<h3 class="ac-meta-heading">Description</h3>
			   			<p><?= get_post_meta($post->ID, 'description', true) ?></p>
			   		<?php endif; ?>
					</div>
				</div>
				<div class="row">
			   		<?php $ac_media->display_meta($owner); ?>
				</div>
			<?php if ($owner) : ?>
				<div class="row owner-actions-footer">
					<div class="hr"></div>
					<div class="col-xs-2">
						<?php wp_nonce_field('delete_ac_media', 'delete_ac_media_nonce'); ?>
						<a id="audubon-core-delete-media" href="#" class="btn btn-danger ajax-btn">Delete Media</a>
					</div>
					<div class="col-xs-2 col-xs-offset-8">
					    <?php wp_nonce_field('update_ac_media', 'update_ac_media_nonce'); ?>
						<a id="audubon-core-update-media" href="#" class="btn btn-primary ac-right ajax-btn">Update Media</a>
					</div>
				</div>
			<?php endif; ?>
			</div><!-- .ac-media-content -->
	<?php if ($owner) : ?>
		</form>
	<?php endif; ?>
		</div><!-- #audubon-core -->
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>