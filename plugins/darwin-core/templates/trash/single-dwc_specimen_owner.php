<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<div class="row">
		<div class="col-xs-2">
			<input type="hidden" id="dwc_specimen_id" name="dwc_specimen_id" value="<?= $post->ID ?>" />
	        <input type="hidden" name="action" value="dwc_update_specimen" />
	        <?php wp_nonce_field('dwc_update_specimen_post', 'dwc_update_specimen_nonce'); ?>
			<input class="btn btn-primary" type="submit" name="update" value="Update Specimen" />
		</div>
		<div class="col-xs-2 col-xs-offset-8">
			<?php wp_nonce_field('dwc_delete_specimen_ajax', 'dwc_delete_specimen_nonce'); ?>
			<a id="myfs-collection-delete-dwc" href="#" class="btn btn-danger ajax-btn">Delete Specimen</a>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-7 dwc-main-meta">
	        <div class="dwc-row">
	        	<label class="dwc-label">Title</label><input class="dwc-input" type="text" placeholder="Title" name="post_title" value="<?= $post->post_title ?>" />
	        </div>
	        <h3>Description</h3>
	        <textarea class="dwc-input" name="description" rows="5"><?= $post->post_content ?></textarea>
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
		
		<div class="col-xs-12 col-sm-5">
			<?php
			$args = array(
				'post_parent' => $post->ID,
				'post_type' => 'ac_multimedia'
			);
			$children = get_children($args);
			
			echo '<div class="slick-holder">';
			foreach ($children as $child) {
				$media_url = get_post_meta( $child->ID, 'resource_url', true );
				if ( $media_url !== '' ) {
					//need to be able to handle images here
					echo do_shortcode('[canvasio3D width="414" height="414" border="1" borderCol="#F6F6F6" dropShadow="0" backCol="#000000" backImg="..." mouse="on" rollMode="off" rollSpeedH="0" rollSpeedV="0" objPath="' . $media_url . '" objScale=".75" objColor="#777777" lightSet="7" reflection="off" refVal="5" objShadow="off" floor="off" floorHeight="42" lightRotate="off" Help="off"] [/canvasio3D]');
				}
				else {
					$args = array(
						'post_parent' => $child->ID,
						'post_type' => 'attachment'
					);
					$children = get_children($args);

					$media = array_shift( $children );
					if ( !is_null( $media ) ) {
						if ($media->post_mime_type === 'application/octet-stream') {
							echo "<div class='ac-wrapper'>" . do_shortcode('[canvasio3D width="320" height="320" border="1" borderCol="#F6F6F6" dropShadow="0" backCol="#000000" backImg="..." mouse="on" rollMode="off" rollSpeedH="0" rollSpeedV="0" objPath="' . $media->guid . '" objScale="1.0" objColor="#777777" lightSet="7" reflection="off" refVal="5" objShadow="off" floor="off" floorHeight="42" lightRotate="off" Help="off"] [/canvasio3D]');
							echo "<a href='/ac_multimedia/" . $child->ID . "'>View " . $child->ID . "</a></div>";
						}
						else {
							echo '<div class="ac-wrapper"><a href="'.$media->guid.'">'.wp_get_attachment_image( $media->ID, array(320,320), false, array('class'=>'wp-image-'.$media->ID) ).'</a>';
							echo "<a href='/ac_multimedia/" . $child->ID . "'>View " . $child->ID . "</a></div>";
						}
					}
					else {
						echo "<div class='ac-placeholder'><p class='ac-placeholder-text'>No image attached. <a href='/ac_multimedia/" . $child->ID . "'>Edit " . $child->ID . "</a></p></div>";
					}
				}
			}
			echo '</div>';
			?>
			<div class="ac-create-btn-wrapper">
				<?php wp_nonce_field('myfs_collection_create_ac_ajax', 'myfs_collection_create_ac_nonce'); ?>
				<a id="myfs-collection-create-ac" href="#" class="btn btn-primary ajax-btn">New Multimedia</a>
			</div>
		</div>
	</div>
	<?php $specimen->display_meta($owner); ?>
    
</form>
<div class="row">
	<div class="col-xs-12">
		<?php if ( is_user_logged_in() && is_single() ) comments_template(); ?>
	</div>
</div>