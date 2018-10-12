<?php

function myfossil_fossil_render_single_settings( $fossil )
{
?>
    <div id="buddypress" class="container page-styling site-main" role="main">
    <h3>Settings</h3>

    <div class="col-xs-12 col-md-6">
        <h4>Delete</h4>
        <div class="alert alert-danger">
            <button type="button" class="btn btn-default btn-block" id="delete-fossil">
                <i class="fa fa-fw fa-trash-o"></i>
                Delete
            </button>

            <p style="padding: 20px; text-align: center">
                This <strong>cannot be undone</strong>, so use caution!
            </p>
        </div>
    </div>
    </div>

    <?php
}
