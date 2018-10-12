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
$max_mb = (wp_max_upload_size() / (1024*1024));
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="container wizard" id="audubon-core">
			<div id="step-1-wrapper">
				<h2>Instructions:</h2>
					<li>Drag and drop an .STL file (3d image) to the specified area below (file size limit <?= $max_mb ?>MB)</li>
					<li>OR you can provide a <?php echo do_shortcode("[sg_popup id=3 event='click']hotlink url[/sg_popup]"); ?> to another site (e.g. dropbox) where the .STL is hosted (file size limit 125MB)</li>
					<li>Drag and drop a thumbnail image (.png, .jpeg, .gif) of your file to the specified area. Make sure the thumbnail dimensions are square to prevent the image from being cropped. A thumbnail is not required, but is strongly encouraged. <!--For instructions on creating a thumbnail from your .STL click <a href="">here</a>.--></li>
					<li>Once you have completed these steps, click Continue.</li>
				</ul>
				<br />
				<div class="row">
					<div class="col-xs-12">
						<div id="ac-dragndrop"><p>Drag &amp; Drop .STL File Here</p></div>
					</div>
					<!--
					<div class="col-xs-12 col-md-6">
						<div id="ac-dragndrop-thumb"><p>Drag &amp; Drop Thumbnail (.png, .jpeg, .gif) Here</p></div>
					</div>
				-->
				</div>
				<p>Provide a <?php echo do_shortcode("[sg_popup id=3 event='click']hotlink URL[/sg_popup]"); ?> below if your .STL is being hosted on another site (file size limit 125MB).</p>
				<div clas="ac-row">
					<?php wp_nonce_field('upload_ac_media_url', 'upload_ac_media_url_nonce'); ?>
					<input type="text" class="ac-full-input" name="ac_media_url" id="ac_media_url" value="" placeholder="Hotlink URL" />
					<a id="ac-media-upload-url" href="#" class="btn btn-primary ajax-btn">Save URL &rarr;</a>
				</div>
				<form>
		        <input type="hidden" id="upload_media_action" name="upload_media_action" value="upload_ac_media" />
				<?php wp_nonce_field('upload_ac_media', 'upload_ac_media_nonce'); ?>
				</form>
				<br><br>
				<div id="status1"></div>
				<!--
				<div class="row">
					<div class="col-xs-12">
						<button type="button" id="step-1-continue" href="#" class="btn btn-success" disabled>Continue</button>
					</div>
				</div>
			-->
			</div>
			<!--
			<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
				<input type="hidden" name="resource_url" id="resource_url" value="" />
				<input type="hidden" name="thumb_id" id="thumb_id" value="" />
				<input type="hidden" name="ac_media_id" id="ac_media_id" value="" />
		        <input type="hidden" name="action" value="ac_media_complete_wizard" />
		        <?php wp_nonce_field('ac_media_complete_wizard_post', 'ac_media_complete_wizard_nonce'); ?>
				<div id="step-2-wrapper" class="inactive">
					<div class="row">
						<div class="col-xs-12 col-sm-6">
					        <div class="ac-row">
					        	<h3>Title</h3>
					        	<input class="ac-full-input" type="text" placeholder="Title" name="post_title" id="post_title" value="" />
					        </div>
						</div>
						<div class="col-xs-12 col-sm-6">
					        <h3>Description</h3>
					        <textarea class="ac-input" name="description" rows="5"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<button type="button" id="step-2-continue" href="#" class="btn btn-success">Continue</button>
						</div>
					</div>
				</div>
				<div id="step-3-wrapper" class="inactive">
					<div class="row">
						<?php
						$media = new AudubonCoreMedia();
						$media->display_meta(true);
						?>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<input type="submit" id="step-3" class="btn btn-success" value="Finish" />
						</div>
					</div>
				</div>
			</form>
		-->
		</div>
	</main>
</div>
<?php get_footer(); ?>