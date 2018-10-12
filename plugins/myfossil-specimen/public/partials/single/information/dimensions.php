<?php

require_once '_common.php';

function fossil_edit_dimensions( $fossil=null )
{
?>
    <div id="edit-fossil-dimensions" class="edit-fossil-popup">
        <div class="edit-fossil">
            <div class="edit-fossil-heading">
                <h4>Dimensions</h4>
            </div>
            <div class="edit-fossil-body">
                <form class="form">
                    <div class="form-group" id="form-group-dimension-length">
                        <label class="control-label">Length</label>
                        <div class="input-group">
                            <input class="form-control" type="text" id="edit-fossil-dimension-length" />
                            <span class="input-group-addon">cm</span>
                        </div>
                    </div>
                    <div class="form-group" id="form-group-dimension-width">
                        <label class="control-label">Width</label>
                        <div class="input-group">
                            <input class="form-control" type="text" id="edit-fossil-dimension-width" />
                            <span class="input-group-addon">cm</span>
                        </div>
                    </div>
                    <div class="form-group" id="form-group-dimension-height">
                        <label class="control-label">Height</label>
                        <div class="input-group">
                            <input class="form-control" type="text" id="edit-fossil-dimension-height" />
                            <span class="input-group-addon">cm</span>
                        </div>
                    </div>

                    <?php edit_comment_box( 'dimension' ); ?>
                </form>
            </div>
            <div class="edit-fossil-footer">
            </div>
        </div>
    </div>
    <?php
}


function fossil_view_dimensions( $fossil=null )
{
?>

    <h3>
        Dimensions
        <i style="display: none" class="fa fa-fw fa-circle-o-notch fa-spin"
                id="fossil-dimensions-loading"></i>
        <i style="display: none" class="fa fa-fw fa-check"
                id="fossil-dimensions-success"></i>
        <i style="display: none" class="fa fa-fw fa-warning"
                id="fossil-dimensions-error"></i>
    </h3>

    <?php save_alert( 'dimension' ); ?>

    <table class="table">
        <tr class="sr-only">
            <th>Dimension</th>
            <th>Value</th>
        </tr>
        <?php foreach ( array( 'length', 'width', 'height' ) as $k ) { ?>
            <tr>
                <td class="fossil-property"><?php echo ucwords( $k ) ?></td>
                <td class="fossil-property-value<?php echo ( current_user_can( 'edit_post', $fossil->id ) ) ? " edit-fossil-dimensions_open editable" : null ?>"
                        id="fossil-dimension-<?php echo $k ?>"
                        data-value="<?php echo ( $fossil->dimension ) ? $fossil->dimension->{ $k } : null ?>"
                        data-edit="<?php echo current_user_can( 'edit_post', $fossil->id )?>"
                        data-popup-ordinal="<?php echo current_user_can( 'edit_post', $fossil->id )?>">
                    <?php if ( $fossil->dimension && $v = $fossil->dimension->{ $k } * 100 ): ?>
                        <?php echo $v ?> cm
                    <?php else: ?>
                        <span class="unknown">Unknown</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <?php
}

function fossil_dimensions( $fossil=null )
{
    fossil_view_dimensions( $fossil );
    fossil_edit_dimensions( $fossil );
}
