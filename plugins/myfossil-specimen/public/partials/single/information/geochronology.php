<?php

require_once '_common.php';

function fossil_view_geochronology( $fossil=null )
{
?>
    <h3>
        Geochronology
        <i class="fa fa-fw fa-circle-o-notch fa-spin"
                id="fossil-geochronology-loading"></i>
        <i style="display: none" class="fa fa-fw fa-check"
                id="fossil-geochronology-success"></i>
        <i style="display: none" class="fa fa-fw fa-warning"
                id="fossil-geochronology-error"></i>
    </h3>

    <?php save_alert( 'geochronology' ); ?>

    <input type="hidden"
            id="fossil-geochronology-name"
            value="<?php echo $fossil->time_interval->name ?>" />
    <table class="table">
        <?php foreach ( array( 'eon', 'era', 'period', 'epoch', 'age' ) as $n => $k ): ?>
            <tr>
                <td class="fossil-property"><?php echo ucwords( $k ) ?></td>
                <td class="fossil-property-value<?php echo ( current_user_can( 'edit_post', $fossil->id ) ) ? " edit-fossil-geochronology_open editable" : null ?>"
                        id="fossil-geochronology-<?php echo $k ?>"
                        data-edit="<?php echo current_user_can( 'edit_post', $fossil->id )?>"
                        data-popup-ordinal="<?php echo current_user_can( 'edit_post', $fossil->id )?>">
                    <span class="unknown">Unknown</span>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php
}

function fossil_edit_geochronology( $fossil=null )
{
?>
    <input type="hidden" id="fossil-geochronology-name" />
    <input type="hidden" id="fossil-geochronology-level" />
    <input type="hidden" id="fossil-geochronology-pbdb" />
    <input type="hidden" id="fossil-geochronology-color" />

    <div id="edit-fossil-geochronology" class="edit-fossil-popup">
        <div class="edit-fossil">
            <div class="edit-fossil-heading">
                <h4>Geochronology</h4>
            </div>
            <div class="edit-fossil-body">
                <form class="form">
                    <div class="form-group">
                        <label class="control-label">Time Interval</label>
                        <select class="form-control" id="edit-fossil-geochronology">
                        </select>
                    </div>
                    <?php edit_comment_box( 'geochronology' ); ?>
                </form>
            </div>
            <div class="edit-fossil-footer">
            </div>
        </div>
    </div>

    <?php
}

function fossil_geochronology( $fossil=null )
{
    fossil_view_geochronology( $fossil );
    fossil_edit_geochronology( $fossil );
}
