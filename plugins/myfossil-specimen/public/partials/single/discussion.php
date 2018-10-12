<?php

function myfossil_fossil_render_single_discussion( $fossil )
{
?>
    <div id="buddypress" class="container page-styling site-main" role="main">
    <?php fossil_view_comments( $fossil ); ?>
    </div>
    <?php
}
