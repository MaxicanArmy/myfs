<?php
/**
 * BuddyPress - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package myfossil
 * @subpackage theme
 */

do_action('bp_before_activity_entry');

global $activities_template;
?>
<li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>" style="list-style: none;">
    <div style="float:left;padding:10px 10px 0 10px;">
        <?=get_avatar( $activities_template->activity->user_id, 60 ) ?>
    </div>
    <div style="overflow:auto;">
        <div class="activity-heading">
            <?=$activities_template->activity->action; ?>
        </div>
    </div>
    <div class="clear"></div>
    <div class="time-since">
        <i class="fa fa-fw fa-clock-o"></i>
        <?=bp_core_time_since( $activities_template->activity->date_recorded ); ?>
    </div>
    <div class="clear"></div>
</li>
