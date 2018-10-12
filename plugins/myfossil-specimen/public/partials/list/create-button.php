<?php

function myfossil_fossil_create_button()
{
?>
    <?php if ( is_user_logged_in() ) : ?>
        <input type="hidden" id="myfossil_specimen_nonce"
                value="<?php echo wp_create_nonce( 'myfossil_specimen' ) ?>" />
        <button class="btn btn-default disabled" id="fossil-create-new">
            Create New Fossil
        </button>
    <?php endif; ?>
    <?php
}
