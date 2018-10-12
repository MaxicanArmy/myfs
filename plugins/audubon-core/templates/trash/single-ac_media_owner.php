<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" enctype="multipart/form-data">
	<input type="hidden" name="action" value="myfs_collection_update_ac" />
	<input type="hidden" id="post_id" name="post_id" value="<?= $post->ID ?>" />
	<div class="row">
		<div class="col-xs-6 col-lg-8">
			<!--<p><a href="/dwc_specimen/<?= $post->post_parent ?>/">&larr;&nbsp;Back to Specimen</a></p>-->
		</div>
		<div class="col-xs-3 col-lg-2">
		    <?php wp_nonce_field('myfs_collection_update_ac_post', 'myfs_collection_update_ac_nonce'); ?>
			<input class="btn btn-primary" type="submit" name="update" value="Update Media" />
		</div>
		<div class="col-xs-3 col-lg-2">
			<?php wp_nonce_field('myfs_collection_delete_ac_ajax', 'myfs_collection_delete_ac_nonce'); ?>
			<a id="myfs-collection-delete-ac" href="#" class="btn btn-danger ajax-btn">Delete Multimedia</a>
		</div>
	</div>
	<div class="row">
		<div class="col-md-7 col-lg-6">
			<?php
			$media_url = get_post_meta( $post->ID, 'resource_url', true );
			if ( $media_url !== '' ) {
				$args = array(
					'post_parent' => $post->ID,
					'post_type' => 'attachment'
				);
				$children = get_children($args);

				$media = array_shift( $children );
				if ( !is_null( $media ) ) {
					if ($media->post_mime_type === 'application/octet-stream') {
						echo do_shortcode('[canvasio3D width="510" height="510" border="1" borderCol="#F6F6F6" dropShadow="0" backCol="#000000" backImg="..." mouse="on" rollMode="off" rollSpeedH="0" rollSpeedV="0" objPath="' . $media_url . '" objScale=".75" objColor="#777777" lightSet="7" reflection="off" refVal="5" objShadow="off" floor="off" floorHeight="42" lightRotate="off" Help="off"] [/canvasio3D]');
						//echo "<a class='btn btn-primary' href='$media_url' download>Download</a>";
					}
					else {
						//echo '<a href="'.$media->guid.'">'.wp_get_attachment_image( $media->ID, array(414,414), false, array('class'=>'wp-image-'.$media->ID) ).'</a><div id="image_holder"></div>';
					}
				}
			}
			else {
				echo "<p>There doesn't seem to be any media associated with this entry.</p>";
			}
			?>
		</div>
		<div class="col-md-5 col-lg-6">
			<div class="dwc-row">
				<label class="ac-label">Title</label><input class="dwc-input" type="text" placeholder="Title" name="post_title" value="<?= $post->post_title ?>" />
			</div>
			<?php $multimedia->display_meta(get_current_user_id() == $post->post_author); ?>
			<!--
			<div class="row">
				<div class="col-xs-12">
		    		<h3>Change Resource</h3>
				    <div class="ac-row">
				        <label class="ac-label">Upload</label><input class="ac-input" type="file" id="wp_custom_attachment" name="wp_custom_attachment" size="25" />
					</div>
					<p style="text-align:center;">OR provide URL</p>
					<div class="dwc-row">
						<label class="ac-label">URL</label><input class="ac-input" type="text" placeholder="Resource URL" name="resource_url" value="<?= get_post_meta( $post->ID, 'resource_url', true ) ?>" />
					</div>
				</div>
		    </div>
		    -->
		</div>
	</div>
</form>