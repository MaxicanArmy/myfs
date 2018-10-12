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
    <div class="activity-entry">
        <div class="activity-heading" style="border:none;">
            <div style="float:left;">
                <?=get_avatar( $activities_template->activity->user_id, 30 ) ?>
            </div>
            <?=$activities_template->activity->action; ?>
            <div class="clear" style="clear:both;"></div> 
            <div class="time-since">
                <i class="fa fa-fw fa-clock-o"></i>
                <?=bp_core_time_since( $activities_template->activity->date_recorded ); ?>
            </div>
            <div class="clear" style="clear:both;"></div> 
        </div>
    </div>
</li>
