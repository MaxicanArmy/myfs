<div id="buddypress-header" class="dark">
	<?php do_action( 'bp_before_member_home_content' ); ?>

	<div id="item-header" class="container" role="complementary">
		<?php bp_get_template_part( 'members/single/member-header' ) ?>
	</div><!-- #item-header -->

	<div id="item-nav" class="container">
        <ul class="nav nav-tabs" id="nav-member">
            <?php bp_get_displayed_user_nav(); ?>
            <?php do_action( 'bp_member_options_nav' ); ?>
        </ul>
	</div><!-- #item-nav -->
</div>

<div id="buddypress" class="container page-styling">

	<div id="item-body" role="main">

		<?php do_action( 'bp_before_member_body' );

		if ( bp_is_user_activity() || !bp_current_component() ) :
			bp_get_template_part( 'members/single/activity' );

		elseif ( bp_is_user_blogs() ) :
			bp_get_template_part( 'members/single/blogs'    );

		elseif ( bp_is_user_friends() ) :
			bp_get_template_part( 'members/single/friends'  );

		elseif ( bp_is_user_groups() ) :
			bp_get_template_part( 'members/single/groups'   );

		elseif ( bp_is_user_messages() ) :
			bp_get_template_part( 'members/single/messages' );

		elseif ( bp_is_user_profile() ) :
			bp_get_template_part( 'members/single/profile'  );

		elseif ( bp_is_user_forums() ) :
			bp_get_template_part( 'members/single/forums'   );

		elseif ( bp_is_user_notifications() ) :
			bp_get_template_part( 'members/single/notifications' );

		elseif ( bp_is_user_settings() ) :
			bp_get_template_part( 'members/single/settings' );

        elseif ( bp_is_current_component( 'fossils' ) ) :
			bp_get_template_part( 'members/single/fossils' );

       // elseif ( bp_is_current_component( 'wall' ) ) :
		//	bp_get_template_part( 'members/single/wall' );

		// If nothing sticks, load a generic template
		else :
			bp_get_template_part( 'members/single/plugins'  );

		endif;

		do_action( 'bp_after_member_body' ); ?>

	</div><!-- #item-body -->

	<?php do_action( 'bp_after_member_home_content' ); ?>

</div><!-- #buddypress -->
