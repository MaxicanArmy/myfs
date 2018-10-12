<?php

function myfossil_places_filter_form()
{
?>
    <form role="form" id="filters">
        <?php wp_nonce_field( 'myfossil_resources_filter', 'myfossil_resources_filter_nonce' ); ?>

        <h4>State</h4>
        <select class="form-control" id="state-filter">
        </select>

        <h4>Type</h4>
        <div id="types-selected">
        </div>
    </form>

    <button type="button" class="btn btn-default" id="clear-filters">
        Reset Filters
    </button>

    <?php
}
