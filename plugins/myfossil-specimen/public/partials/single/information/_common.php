<?php

function save_alert( $id, $message=null )
{
?>
    <div class="alert-save" id="edit-fossil-<?php echo $id ?>-save-alert" style="display: none">
        <div class="message">
            You have unsaved changes.
        </div>
        <div class="actions">
            <button class="btn btn-default btn-sm"
                onClick="window.location.reload()"
            >
                <i class="fa fa-fw fa-trash"></i>
                Reset All
            </button>

            <button class="btn btn-default btn-sm" id="edit-fossil-<?php echo $id ?>-save">
                <i class="fa fa-fw fa-save"></i>
                Save
            </button>
        </div>
    </div>

    <?php
    /*
    <button class="btn btn-default btn-sm" id="edit-fossil-<?=$id ?>-reset">
        <i class="fa fa-fw fa-undo"></i>
        Undo
    </button>
    */
}


function edit_comment_box( $id )
{
?>
        <div id="edit-fossil-<?php echo $id ?>-comment-toggle">
            <button type="button" class="btn btn-sm btn-block btn-default">
                <i class="fa fa-fw fa-comment"></i>
                Add comment
            </button>
        </div>
        <div style="display: none"
                class="form-group"
                id="edit-fossil-<?php echo $id ?>-comment-form-group">
            <label class="control-label" for="edit-fossil-<?php echo $id ?>-comment">
                Comment
            </label>
            <textarea class="form-control"
                    id="edit-fossil-<?php echo $id ?>-comment"
                    name="edit-fossil-<?php echo $id ?>-comment"
                    placeholder="Comment"></textarea>
        </div>
    <?php
}
