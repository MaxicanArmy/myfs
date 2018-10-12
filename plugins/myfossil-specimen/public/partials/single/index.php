<?php
use myFOSSIL\Plugin\Specimen\Fossil;

require_once '_header.php';

require_once 'information.php';
require_once 'history.php';
require_once 'discussion.php';
require_once 'images.php';
require_once 'settings.php';

function myfossil_fossil_render_single( $fossil_id, $view )
{
    $fossil = new Fossil( $fossil_id );

?>
    <div id="fossil" class="content-area">
        <?php fossil_header( $fossil, $view ); ?>

    <?php
    switch ( $view ) {
    case 'information':
        myfossil_fossil_render_single_information( $fossil );
        break;
    case 'history':
        myfossil_fossil_render_single_history( $fossil );
        break;
    case 'discussion':
        myfossil_fossil_render_single_discussion( $fossil );
        break;
    case 'images':
        myfossil_fossil_render_single_images( $fossil );
        break;
    case 'settings':
        myfossil_fossil_render_single_settings( $fossil );
        break;
    }
?>

    </div>

    <?php
}
