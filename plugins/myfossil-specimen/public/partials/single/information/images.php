<?php

require_once '_common.php';

function fossil_view_images( $fossil )
{
    $image_id = $fossil->image_id;
    $image_src = $fossil->image;
?>
    <h3 class="sr-only">Image</h3>
    <div class="activity-entry">
        <div class="activity-body">
            <?php if ( $image_id > 0 && $image_src ) : ?>
                <img id="fossil-featured-image" class="img-responsive fossil-image"
                        src="<?php echo $image_src ?>" style="padding: 10px;"
                        data-attachment-id="<?php echo $image_id ?>" />
            <?php else: ?>
                <p class="text-center" style="padding: 10px; color: #c0c0c0">No image available.</p>
            <?php endif; ?>
        </div>
        <div class="activity-footer text-center">
            <a class="btn btn-sm btn-default" href="/fossils/<?php echo $fossil->id ?>/images">
                <?php if ( current_user_can( 'edit_post', $fossil->id ) ) : ?>
                    Manage Images
                <?php else : ?>
                    View More Images
                <?php endif; ?>
            </a>
        </div>
    </div>
    <?php
}

function fossil_images( $fossil=null )
{
    fossil_view_images( $fossil );
}
