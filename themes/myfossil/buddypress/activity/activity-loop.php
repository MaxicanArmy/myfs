<?php do_action('bp_before_activity_loop'); ?>

<?php

// check if viewing main activity page or profile > activity
// adjust bp_has_activities() parameters accordingly


global $bp; 
//echo 'current action: '. $bp->current_action;

if( ! bp_is_user() && ! bp_is_group() )
	$params = '&scope=just-me,friends,mentions&per_page=20';
elseif( bp_is_user() && $bp->current_action == 'just-me' )
	$params = '&scope=just-me,mentions&per_page=20';
else
	$params = '';

?>

<?php if ( bp_has_activities( bp_ajax_querystring( 'activity') . $params ) ) : ?>


    <?php if (empty($_POST['page'])): ?>
        <ul id="activity-stream" class="activity-list item-list">
    <?php endif; ?>

    <?php while ( bp_activities() ) : ?>
        <?php bp_the_activity(); ?>
        <?php bp_get_template_part('activity/entry'); ?>
    <?php endwhile; ?>

    <?php if (bp_activity_has_more_items()): ?>
        <li class="load-more">
            <a href="<?php bp_activity_load_more_link() ?>"><?php _e('Load More', 'buddypress'); ?></a>
        </li>
    <?php endif; ?>

    <?php if (empty($_POST['page'])): ?>
        </ul>
    <?php endif; ?>
<?php else: ?>
    <div id="message" class="info">
        <p><?php _e('Sorry, there was no activity found. Please try a different filter.', 'buddypress'); ?></p>
    </div>
<?php endif; ?>

<?php do_action('bp_after_activity_loop'); ?>

<?php if (empty($_POST['page'])): ?>
    <form action="" name="activity-loop-form" id="activity-loop-form" method="post">
        <?php wp_nonce_field('activity_filter', '_wpnonce_activity_filter'); ?>
    </form>
<?php endif; ?>
