<?php
/**
 * BuddyPress - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

/**
 * Fires before the display of an activity entry.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_activity_entry' );
global $activities_template;
?>

<li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">
	<div class="activity-content homepage-activity-item">
		<div style="float:left;padding:15px;">
			<?=get_avatar( $activities_template->activity->user_id, 50 ) ?>
		</div>
		<div style="overflow:auto;">
			<div class="activity-heading">
		        <div style="float:right;padding-top:8px;">
		            <i class="fa fa-fw fa-clock-o"></i>
		            <?=bp_core_time_since( $activities_template->activity->date_recorded ); ?>
		        </div>
		        <div class="activity-action">
		        	<?=$activities_template->activity->action; ?>		
		        </div>
		        <div class="clear"></div>
			</div>
			<?php if ( bp_activity_has_content() ) : ?>
			<div class="activity-inner">
				<?php bp_activity_content_body(); ?>
			</div>
			<?php endif; ?>
		</div>
		<div class="clear"></div>

		<?php

		/**
		 * Fires after the display of an activity entry content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_activity_entry_content' ); ?>

		<div class="activity-meta">

			<?php if ( bp_get_activity_type() == 'activity_comment' ) : ?>

				<a href="<?php bp_activity_thread_permalink(); ?>" class="button view bp-secondary-action" title="<?php esc_attr_e( 'View Conversation', 'buddypress' ); ?>"><?php _e( 'View Conversation', 'buddypress' ); ?></a>

			<?php endif; ?>

			<?php if ( is_user_logged_in() ) : ?>

				<?php if (bp_activity_can_comment()): ?>
                
                    <?php $act_type = bp_get_activity_type(); ?>   
                               
                    <?php if( $act_type != 'bbp_topic_create' && $act_type != 'bbp_reply_create' ) : ?>
                
                        <a href="<?php bp_activity_comment_link(); ?>" class="button acomment-reply bp-primary-action" id="acomment-comment-<?php bp_activity_id(); ?>">
                        <?php if ($c = bp_activity_get_comment_count()): ?>
                            <i class="fa fa-fw fa-comments-o"></i>
                            <?php printf(__('Comment <span class="badge">%s</span>', 'buddypress') , $c); ?>
                        <?php else: ?>
                            <i class="fa fa-fw fa-comment-o"></i>
                            <?php printf(__('Comment', 'buddypress') , $c); ?>
                        <?php endif; ?>
                        </a>
                    
                    <?php else : ?>
                        <?php echo '<i class="fa fa-fw fa-comment-o"></i><a href="' . $activities_template->activity->primary_link . '">Comment</a>&nbsp;'; ?>
                    <?php endif; ?>
                    
                <?php else : ?>                    
                    <?php $act_type = bp_get_activity_type(); ?>
                    <?php if( $act_type == 'bbp_topic_create' || $act_type == 'bbp_reply_create' ) : ?>
                        <?php echo '<a href="' . $activities_template->activity->primary_link . '"><i class="fa fa-fw fa-comment-o"></i> Reply</a>&nbsp;'; ?>
                    <?php endif; ?>  
                <?php endif; ?>

				<?php if ( bp_activity_can_favorite() ) : ?>

					<?php if ( !bp_get_activity_is_favorite() ) : ?>

						<a href="<?php bp_activity_favorite_link(); ?>" class="button fav bp-secondary-action" title="<?php esc_attr_e( 'Favorite', 'buddypress' ); ?>"><i class="fa fa-fw fa-star-o"></i><?php _e( 'Favorite', 'buddypress' ); ?></a>

					<?php else : ?>

						<a href="<?php bp_activity_unfavorite_link(); ?>" class="button unfav bp-secondary-action" title="<?php esc_attr_e( 'Remove Favorite', 'buddypress' ); ?>"><i class="fa fa-fw fa-star"></i><?php _e( 'Remove Favorite', 'buddypress' ); ?></a>

					<?php endif; ?>

				<?php endif; ?>

				<?php if ( bp_activity_user_can_delete() ) bp_activity_delete_link(); ?>

				<?php

				/**
				 * Fires at the end of the activity entry meta data area.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_activity_entry_meta' ); ?>

			<?php endif; ?>

		</div>

	</div>

	<?php

	/**
	 * Fires before the display of the activity entry comments.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_activity_entry_comments' ); ?>

	<?php if ( ( bp_activity_get_comment_count() || bp_activity_can_comment() ) || bp_is_single_activity() ) : ?>

		<div class="activity-comments"><!-- checking usage -->

			<?php bp_activity_comments(); ?>

			<?php if ( is_user_logged_in() && bp_activity_can_comment() ) : ?>

				<form action="<?php bp_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php bp_activity_id(); ?>" class="ac-form"<?php bp_activity_comment_form_nojs_display(); ?>>
					<div class="ac-reply-avatar"><?php bp_loggedin_user_avatar( 'width=30&height=30'); ?></div>
					<div class="ac-reply-content">
						<div class="ac-textarea">
							<label for="ac-input-<?php bp_activity_id(); ?>" class="bp-screen-reader-text"><?php
								/* translators: accessibility text */
								_e( 'Comment', 'buddypress' );
							?></label>
							<textarea id="ac-input-<?php bp_activity_id(); ?>" class="ac-input bp-suggestions" name="ac_input_<?php bp_activity_id(); ?>"></textarea>
						</div>
						<input type="submit" name="ac_form_submit" value="<?php esc_attr_e( 'Post', 'buddypress' ); ?>" /> &nbsp; <a href="#" class="ac-reply-cancel"><?php _e( 'Cancel', 'buddypress' ); ?></a>
						<input type="hidden" name="comment_form_id" value="<?php bp_activity_id(); ?>" />
					</div>

					<?php

					/**
					 * Fires after the activity entry comment form.
					 *
					 * @since 1.5.0
					 */
					do_action( 'bp_activity_entry_comments' ); ?>

					<?php wp_nonce_field( 'new_activity_comment', '_wpnonce_new_activity_comment' ); ?>

				</form>

			<?php endif; ?>

				<?php

				/**
				 * Fires after the display of the activity entry comments.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_after_activity_entry_comments' ); ?>

		</div><!-- .activity-comments -->

	<?php endif; ?>

</li><!-- .activity-item -->

<?php

/**
 * Fires after the display of an activity entry.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_activity_entry' ); ?>
