<form action="<?php bp_messages_form_action('compose' ); ?>" method="post" id="send_message_form" class="form standard-form" role="main" enctype="multipart/form-data">

	<?php do_action( 'bp_before_messages_compose_content' ); ?>

	<label class="control-label" for="send-to-input"><?php _e("Send To (Username or Friend's Name)", 'buddypress' ); ?></label>
	<ul class="first acfb-holder">
		<li>
			<?php bp_message_get_recipient_tabs(); ?>
			<input type="text" name="send-to-input" class="form-control send-to-input bp-suggestions" id="send-to-input" />
		</li>
	</ul>

	<?php if ( bp_current_user_can( 'bp_moderate' ) ) : ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" id="send-notice" name="send-notice" value="1" /> <?php _e( "This is a notice to all users.", "buddypress" ); ?>
            </label>
        </div>
	<?php endif; ?>

    <div class="form-group">
        <label class="control-label" for="subject"><?php _e( 'Subject', 'buddypress' ); ?></label>
        <input class="form-control" type="text" name="subject" id="subject" value="<?php bp_messages_subject_value(); ?>" />
    </div>

    <div class="form-group">
        <label class="control-label" for="content"><?php _e( 'Message', 'buddypress' ); ?></label>
        <textarea class="form-control" name="content-holder" id="message_content_holder" rows="15" cols="40" style="display:none;"><?php bp_messages_content_value(); ?></textarea>
        <?php 
        global $is_IE;
        if ($is_IE) : ?>
            <textarea name="content" id="message_content" rows="15" cols="40"><?php bp_messages_content_value(); ?></textarea>
        <?php else :
            do_action( 'bp_messaging_textarea' );
        endif; ?>
        <!-- <textarea class="form-control" name="content" id="message_content" rows="15" cols="40"><?php bp_messages_content_value(); ?></textarea> -->
    </div>

	<input type="hidden" name="send_to_usernames" id="send-to-usernames" value="<?php bp_message_get_recipient_usernames(); ?>" class="<?php bp_message_get_recipient_usernames(); ?>" />

	<?php do_action( 'bp_after_messages_compose_content' ); ?>

	<div class="submit">
		<input class="btn btn-primary" type="submit" value="<?php esc_attr_e( "Send Message", 'buddypress' ); ?>" name="send" id="send" />
	</div>

	<?php wp_nonce_field( 'messages_send_message' ); ?>
</form>

<script type="text/javascript">
	document.getElementById("send-to-input").focus();
</script>

