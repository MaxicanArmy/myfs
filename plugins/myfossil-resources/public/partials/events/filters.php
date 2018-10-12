<?php

function myfossil_events_filter_form()
{
?>

    <form role="form" id="filters">
        <?php wp_nonce_field( 'myfossil_resources_filter', 'myfossil_resources_filter_nonce' ); ?>
        <h4>State</h4>
        <select class="form-control" id="state"></select>

        <h4>When</h4>
        <select class="form-control" id="month-year"> </select>

        <h4>Type</h4>
        <div id="types-selected"></div>
    </form>

    <!-- Clear filters -->
    <button type="button" class="btn btn-primary" id="clear-filters">
    Clear Filters
    </button>

    <?php
}
