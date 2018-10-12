<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="container" id="audubon-core">
			<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="dwc_specimen_id" value="<?=get_query_var('dwc_specimen', '') ?>" />
				<div class="row">
					<div class="col-xs-12">
						<div class="row">
							<div class="col-xs-12 col-lg-6 col-lg-offset-3">
							    <div class="ac-row">
							        <label class="ac-label">Upload</label><input class="ac-input" type="file" id="wp_custom_attachment" name="wp_custom_attachment" size="25" />
								</div>
								<p style="text-align:center;">OR provide hotlink URL</p>
								<div class="dwc-row">
									<label class="ac-label">URL</label><input class="ac-input" type="text" placeholder="Resource URL" name="resource_url" value="" />
								</div>
								<input type="hidden" name="action" value="ac_create_media" />
							    <?php wp_nonce_field('ac_media_create_post', 'ac_media_create_nonce'); ?>
								<input class="btn btn-primary" type="submit" name="update" value="Add Media" />
							</div>
					    </div>
					</div>
				</div>
			</form>
		</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>