<?php

require_once '_common.php';

function fossil_view_comments( $fossil=null )
{
?>

    <h3>Discussion</h3>

    <div class="activity" role="main">
    <div>
        <?php if ( is_user_logged_in() ): ?>
            <?php fossil_post_comment_form( $fossil ) ?>
        <?php endif; ?>
    </div>

    <?php
    /*
    $item_id_keys = array( 'id', 'taxa_id', 'location_id',
        'time_interval_id', 'stratum_formation_id', 'stratum_group_id',
        'stratum_member_id', 'dimension_id', 'reference_id' );
    */
    $item_id_keys = array( 'id', 'taxa_id', 'location_id',
        'time_interval_id', 'stratum_formation_id', 'stratum_group_id',
        'stratum_member_id', 'dimension_id', 'reference_id' );
    $item_ids = array();
    foreach ( $item_id_keys as $key ) {
        if ( $fossil->{ $key } ) {
            array_push( $item_ids, $fossil->{ $key } );
        }
    }

    $item_query = implode( ',', $item_ids );

    global $activities_template;
?>

    <?php if ( bp_has_activities( 'activity' .
            '&primary_id=' . $item_query ) ) : ?>
        <ul id="activity-stream" class="activity-list item-list">

        <?php while ( bp_activities() ): ?>
            <?php bp_the_activity(); ?>
            <?php bp_get_template_part( 'activity/entry' ); ?>
        <?php endwhile; ?>

        <?php if ( bp_activity_has_more_items() ): ?>
            <li class="load-more">
                <a href="<?php bp_activity_load_more_link() ?>"><?php _e( 'Load More', 'buddypress' ); ?></a>
            </li>
        <?php endif; ?>

        </ul>
    <?php else: ?>
        <div id="message" class="alert alert-info">
            <p><?php _e( 'Sorry, there was no activity found. Please try a different filter.', 'buddypress' ); ?></p>
        </div>
    <?php endif; ?>

    </div>

    <?php
}


function fossil_post_comment_form( $fossil=null )
{
    if ( ! is_user_logged_in() ) return;
?>
    <form method="post" role="complementary" class="form">
        <div id="fossil-comment-box">
            <div class="status-update">
                <div class="status-update-body">
                    <div id="whats-new-user-avatar">
                        <?php bp_loggedin_user_avatar( 'width=50&height=50' ); ?>
                    </div>
                    <div id="whats-new-textarea" class="form-group">
                        <textarea
                                class="form-control bp-suggestions"
                                name="whats-new" id="fossil-comment-textarea"><?php if ( isset( $_GET['r'] ) ): ?> @<?php echo esc_textarea( $_GET['r'] ); ?> <?php endif; ?></textarea>
                    </div>

                    <div id="whats-new-options">
                        <div id="whats-new-submit">
                            <input type="hidden" id="fossil-id"
                                value="<?php echo $fossil->id ?>" />
                            <button type="button" class="btn btn-default"
                                    name="aw-whats-new-submit"
                                    id="fossil-comment-submit">
                                Comment
                            </button>
                        </div>
                    </div><!-- #whats-new-options -->
                </div>
            </div>
        </div><!-- #whats-new-content -->

        <?php wp_nonce_field( 'post_update', '_wpnonce_post_update' ); ?>
    </form><!-- #whats-new-form -->

    <?php
}

function fossil_comments( $fossil=null )
{
    fossil_view_comments( $fossil );
}
