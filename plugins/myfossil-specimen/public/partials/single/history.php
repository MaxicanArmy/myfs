<?php

function myfossil_fossil_render_single_history( $fossil )
{
    global $activities_template;

    $item_id_keys = array( 'id', 'taxon_id', 'location_id',
        'time_interval_id', 'stratum_formation_id', 'stratum_group_id',
        'stratum_member_id', 'dimension_id', 'reference_id' );
    $item_ids = array();
    foreach ( $item_id_keys as $key )
        if ( $fossil->{ $key } )
        array_push( $item_ids, $fossil->{ $key } );

    $item_query = implode( ',', $item_ids );
?>

    <?php if ( bp_has_activities( bp_ajax_querystring( 'activity' ) .
            '&primary_id=' . $item_query ) ) : ?>
    <div id="buddypress" class="container page-styling site-main" role="main">
        <table class="table">

            <tr>
                <th>User</th>
                <th>Date/Time</th>
                <th>Action</th>
            </tr>

        <?php foreach ( $activities_template->activities as $activity ) : ?>
            <?php if ( $activity->item_id == $fossil->id ): ?>
                <tr>
                    <td>
                        <?php echo get_avatar( $activity->user_id, 30 ) ?>
                    </td>
                    <td>
                        <i class="fa fa-fw fa-clock-o"></i>
                        <?php echo $activity->date_recorded ?>
                    </td>
                    <td>
                        <?php echo $activity->action ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>

        </table>
    </div>

    <?php endif;
}
