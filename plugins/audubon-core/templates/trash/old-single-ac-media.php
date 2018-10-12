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
		<div class="container" id="myfossil-collection">
		<?php
		if ($owner) :
			require_once ('single-dwc_specimen_owner.php');
		else : ?>
			<h1><?= $post->post_title ?></h1>
			<div class="row">
				<div class="col-xs-12 col-sm-7 dwc-main-meta">
			        <p><?= $post->post_content ?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<?php
					$args = array(
						'post_parent' => $post->ID,
						'post_type' => 'ac_multimedia'
					);
					$children = get_children($args);
					
					echo '<div class="slick-holder-3">';
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
						}
					}
					echo '</div>';
					?>
				</div>
			</div>
			<?php $specimen->display_guest(); ?>
		<?php endif; ?>
		</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>