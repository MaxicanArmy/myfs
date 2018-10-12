<?php

use myFOSSIL\Plugin\Specimen\Stratum;

require_once '_common.php';

function fossil_view_lithostratigraphy( $fossil=null )
{
?>


    <h3>
        Lithostratigraphy
        <i style="display: none" class="fa fa-fw fa-circle-o-notch fa-spin"
                id="fossil-lithostratigraphy-loading"></i>
        <i style="display: none" class="fa fa-fw fa-check"
                id="fossil-lithostratigraphy-success"></i>
        <i style="display: none" class="fa fa-fw fa-warning"
                id="fossil-lithostratigraphy-error"></i>
    </h3>

    <?php save_alert( 'lithostratigraphy' ); ?>

    <table class="table">
        <?php foreach ( Stratum::get_ranks() as $n => $k ): ?>
            <tr>
                <td class="fossil-property"><?php echo ucwords( $k ) ?></td>
                <td>
                    <?php if ( current_user_can( 'edit_post', $fossil->id ) ) : ?>
                    <div class="input-group">
                    <input type="text"
                        class="form-control fossil-property-value"
                        id="fossil-stratum-<?php echo $k ?>"
                        data-edit="<?php echo current_user_can( 'edit_post', $fossil->id )?>"
                        data-popup-ordinal="<?php echo current_user_can( 'edit_post', $fossil->id )?>"
                        data-name="<?php echo ( property_exists( $fossil->strata, $k ) ) ? $fossil->strata->{ $k }->name : null ?>"
                        placeholder="Unknown"
                        value="<?php echo property_exists( $fossil->strata, $k ) ? $fossil->strata->{ $k }->name : null ?>" />
                        <span class="input-group-btn">
                            <button class="btn btn-default<?=" edit-fossil-stratum-{$k}_open"; ?>">
                                <i class="fa fa-fw fa-magic"></i>
                            </button>
                        </span>
                    <?php else: ?>
                        <span>
                            <?php echo ( property_exists( $fossil->strata, $k ) ) ? $fossil->strata->{ $k }->name : 'Unknown' ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php
}


function fossil_edit_lithostratigraphy( $fossil=null )
{
    foreach ( Stratum::get_ranks() as $rank ):
?>

    <div id="edit-fossil-stratum-<?php echo $rank ?>" class="edit-fossil-popup">
        <div class="edit-fossil">
            <div class="edit-fossil-heading">
                <h4>Lithostratigraphy</h4>
            </div>
            <div class="edit-fossil-body">
                <form class="form">
                    <div class="form-group">
                        <label class="control-label"><?php echo ucfirst( $rank ) ?></label>
                        <input data-rank="<?php echo $rank ?>"
                                class="form-control"
                                type="text"
                                id="edit-fossil-stratum-<?php echo $rank ?>"
                                placeholder="Begin typing a <?php echo ucfirst( $rank ) ?>" />
                    </div>
                    <div class="form-group">
                        <label class="control-label">Sort By</label>
                        <select class="form-control" data-rank="<?php echo $rank ?>"
                                id="edit-fossil-stratum-<?php echo $rank ?>-sortby">
                            <option value="name">Name</option>
                            <option value="occs">Number of Occurences</option>
                            <option value="colls">Number of Collections</option>
                        </select>
                    </div>
                    <?php edit_comment_box( 'lithostratigraphy' ); ?>
                </form>
            </div>
            <div class="edit-fossil-footer">
                <ul id="edit-fossil-stratum-<?php echo $rank ?>-results">
                </ul>
            </div>
        </div>
    </div>

    <?php
    endforeach;
}

function fossil_lithostratigraphy( $fossil=null )
{
    fossil_view_lithostratigraphy( $fossil );
    fossil_edit_lithostratigraphy( $fossil );
}
