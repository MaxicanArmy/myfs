<?php
use myFOSSIL\Plugin\Specimen\FossilTaxa;

require_once "_common.php";


function fossil_edit_taxonomy( $fossil=null )
{
    ?>

    <div id="edit-fossil-taxon" class="edit-fossil-popup">
        <div class="edit-fossil">
            <div class="edit-fossil-heading">
                <h4>Taxonomy</h4>
            </div>
            <div class="edit-fossil-body">
                <form class="form">
                    <div class="form-group">
                        <label class="control-label">Taxon</label>
                        <input
                            class="form-control"
                            id="edit-fossil-taxon-name"
                            placeholder="Begin typing your Taxon"
                            type="text"
                        />
                    </div>
                    <?php edit_comment_box( "taxon" ); ?>
                </form>
            </div>
            <div class="edit-fossil-footer">
                <ul id="edit-fossil-taxon-results">
                </ul>
            </div>
        </div>
    </div>

    <?php
}

function fossil_view_taxonomy( $fossil=null )
{
?>

    <?php if ( current_user_can( "edit_post", $fossil->id ) ) : ?>
        <button class="btn btn-default edit-fossil-taxon_open pull-right">
            <i class="fa fa-fw fa-magic"></i>
            Taxon Helper
        </button>
    <?php endif; ?>

    <h3>
        Classification
        <i style="display: none" class="fa fa-fw fa-circle-o-notch fa-spin"
                id="fossil-taxon-loading"></i>
        <i style="display: none" class="fa fa-fw fa-check"
                id="fossil-taxon-success"></i>
        <i style="display: none" class="fa fa-fw fa-warning"
                id="fossil-taxon-error"></i>
    </h3>

    <?php save_alert( "taxon" ); ?>

    <table id="fossil-taxon" class="table">
        <tr class="sr-only">
            <th>Taxonomy Level</th>
            <th>Value</th>
            <th>Options</th>
        </tr>

        <?php foreach ( FossilTaxa::get_ranks() as $k ): ?>
            <?php if ( current_user_can( "edit_post", $fossil->id ) ) : ?>
                <tr>
                    <td class="fossil-property"><?php echo ucwords( $k ) ?></td>
                    <td class="fossil-property-value">
                        <?php if ( $fossil->taxa->{ $k } ) : ?>
                            <input type="text"
                                class="form-control taxon"
                                id="fossil-taxon-<?php echo $k ?>"
                                name="fossil-taxon-<?php echo $k ?>"
                                placeholder="Unknown"
                                value="<?php echo $fossil->taxa->{ $k }->name ?>" />
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <td class="fossil-property"><?php echo ucwords( $k ) ?></td>
                    <td class="fossil-property-value"
                        id="fossil-taxon-<?php echo $k ?>">
                        <?php if ( $fossil->taxa->{ $k } && ( $v = $fossil->taxa->{ $k }->name ) ): ?>
                            <?php echo $v ?>
                        <?php else: ?>
                            <span class="unknown">Unknown</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>

    <?php
}

function fossil_taxonomy( $fossil=null )
{
    fossil_view_taxonomy( $fossil );
    fossil_edit_taxonomy( $fossil );
}
