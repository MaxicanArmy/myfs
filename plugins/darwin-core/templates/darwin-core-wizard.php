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
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="container wizard" id="darwin-core">
			<p>The only requirement for starting an entry is an image (or 3d image) of the specimen. You can upload files of type .jpg|.png|.gif or .stl (3d images).</p>
			<p>If you are already hosting the file on another site (for example dropbox) you can provide the URL instead of uploading. Please be aware that the URL must be a direct hotlink to the media or it may not display properly later.</p>
			<div id="dwc-dragndrop"><p>Drag &amp; Drop File Here</p></div>
			<form>
				<?php wp_nonce_field('upload_media_for_dwc_specimen', 'upload_media_for_dwc_specimen_nonce'); ?>
				<input type="hidden" id="upload_media_action" name="upload_media_action" value="upload_media_for_dwc_specimen" />
				<input type="hidden" id="dwc_specimen_id" name="dwc_specimen_id" value="0" />
			</form>
			<br><br>
			<div id="status1"></div>
		</div>
	</main>
</div>
<?php get_footer(); ?>