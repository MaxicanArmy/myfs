<?php do_action('bp_before_directory_activity'); ?>

<div class="container">

    <?php do_action('bp_before_directory_activity_content'); ?>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">
            <?php if (is_user_logged_in()): ?>
                <?php bp_get_template_part('activity/left-menu'); ?>
            <?php endif; ?>
            <div style="margin-top:30px;border-top:1px solid #333;"><h4>Newsletter Signup</h4><?php echo do_shortcode("[yikes-mailchimp form='1']"); ?>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10" style="border-left: 1px solid #eee;">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                    <?php echo do_shortcode("[huge_it_slider id='4']"); ?>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                    <ul class="pull-right activity-search-dropdown" style="width:100%">
                        <li class="dropdown">
                        <a class="btn btn-primary" role="button" data-toggle="dropdown" href="#"><span class="fa-stack"><i class="fa fa-search fa-stack-1x fa-inverse"></i></span>Search <span class="caret"></span></a>
                        <ul id="menu1" class="dropdown-menu" role="menu">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="/fossils?mfs=1">Fossils</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="/members?mfs=1">Members</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="/groups?mfs=1">Groups</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="/forums?mfs=1">Forums</a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="/documents?mfs=1">Documents</a></li>
                        </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12" style="border-left: 1px solid #eee;">
                    <?php if ( bp_is_active( 'messages' ) ) : ?>
                    <?php bp_message_get_notices(); ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Buddypress recent activity widget -->
            <div id="atmo-activity-widget">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3">
                        <?php if ( bp_has_members( 'max=10&type=newest' ) ) : ?> 
                            <?php do_action( 'bp_before_directory_members_list' ); ?>
                            <h4 style="text-align: center;">Newest Members</h4>
                                    <?php echo '<ul style="overflow:hidden; overflow-y:scroll;height:250px;">'; ?>
                                    <?php while ( bp_members() ) : bp_the_member(); ?>
                                        <li style="list-style: none;">
                                            <div class="activity-entry">
                                                <div class="activity-heading" style="border:none;">
                                                    <a href="<?php bp_member_permalink(); ?>" title="<?php bp_member_name(); ?>" style="float:left;"><?php bp_member_avatar( '&width=30&height=30' ); ?></a>
                                                    <a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a><br /><?php bp_member_profile_data(array('field' => 'Location')); ?>
                                                    <div class="clear" style="clear:both;"></div>
                                                </div>
                                            </div> 
                                        </li>
                                    <?php endwhile; ?>
                                    <?php echo '</ul>'; ?>
                                
                            <?php do_action( 'bp_after_directory_members_list' ); ?>
                             
                            <?php bp_member_hidden_fields(); ?>
                         
                        <?php else: ?>
                         
                            <div id="message" class="info">
                                <p><?php _e( "Sorry, no members were found.", 'buddypress' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-5">
                        <h4 style="text-align: center;">Latest Forum Posts</h4>
                        <?php
                        $reply_args = array(
                            'posts_per_page'   => 10,
                            'orderby'          => 'date',
                            'order'            => 'DESC',
                            'post_type'        => array( 'reply' ),
                            'date_query'       => array(
                                                    array(
                                                        'after' => '1 month ago') ),
                            'post_status'      => 'publish'
                        );
                        // The Query
                        $reply_query = new WP_Query( $reply_args );
                        
                        // The Loop
                        if ( $reply_query->have_posts() ) {
                            echo '<ul style="overflow:hidden; overflow-y:scroll;height:250px;">';
                            while ( $reply_query->have_posts() ) : ?>
                                <li style="list-style: none;">
                                    <div class="activity-entry">
                                        <div class="activity-heading" style="border:none;">
                                            <?php $reply_query->the_post();?>

                                            <a href="<?php echo bp_core_get_user_domain( $post->post_author ) ?>" title="<?php echo bp_core_get_user_displayname( $post->post_author ) ?>"><span style="float:left;">
                                            <?php echo bp_core_fetch_avatar( 'item_id='.$post->post_author.'&width=30&height=30' ) ?></span>
                                            <?php echo bp_core_get_user_displayname( $post->post_author ) ?></a> posted: <br />
                                            <?php
                                                echo '<a href="' . bbp_get_topic_permalink() . '">';
                                                if (strlen($post->post_content) > 120) {
                                                    echo substr($post->post_content, 0, 120) . '...'; 
                                                } else {
                                                    echo substr($post->post_content, 0, strlen($post->post_content) );
                                                }
                                                echo '</a><br />in forum:<br />';
                                                $post = get_post( $post->post_parent );

                                                echo '<a href="' . bbp_get_topic_permalink() . '">' . get_the_title() . '</a><br />';
                                                $reply_query->reset_postdata();
                                            ?>
                                            <div class="clear" style="clear:both;"></div> 
                                        </div>
                                    </div>
                                </li>
                            <?php endwhile; 
                            echo '</ul>';
                        } else {
                            // no posts found
                        }
                        /* Restore original Post Data */
                        wp_reset_postdata();
                        ?>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
                        <h4 style="text-align: center;">Recent Activity</h4>
                        <?php if ( bp_has_activities( 'activity&max=10' ) ) : 
                            echo '<ul style="overflow:hidden; overflow-y:scroll;height:250px;">'; ?>
                            <?php while ( bp_activities() ) : bp_the_activity(); ?>
                         
                                <?php bp_get_template_part( 'activity/recent-entry'); ?>
                         
                            <?php endwhile; ?>
                        <?php endif; 
                        echo '</ul>'; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <p style="border-top:1px solid #eee;color:#888888;text-align:center;padding-top:11px;">In the last 30 days myFOSSIL has grown by   
                        <?php
                        $args = array(
                            'orderby'          => 'date',
                            'order'            => 'DESC',
                            'post_type'        => array( 'reply' ),
                            'date_query'       => array(
                                                    array(
                                                        'after' => '1 month ago') ),
                            'post_status'      => 'publish'
                        );
                        // The Query
                        $the_query = new WP_Query( $args );
                        
                        // The Loop
                        if ( $the_query->have_posts() ) {
                            $new_forums = $the_query->found_posts . ' Forum Posts';
                        } else {
                            $new_forums = '0 Forum Posts';
                        }
                        /* Restore original Post Data */
                        wp_reset_postdata();

                        $args = array(
                            'orderby'          => 'date',
                            'order'            => 'DESC',
                            'post_type'        => array( 'myfossil_fossil' ),
                            'date_query'       => array(
                                                    array(
                                                        'after' => '1 month ago') ),
                            'post_status'      => 'publish'
                        );
                        // The Query
                        $the_query = new WP_Query( $args );
                        
                        // The Loop
                        if ( $the_query->have_posts() ) {
                            $new_fossils = $the_query->found_posts . ' Fossils';
                        } else {
                            $new_fossils = '0 New Fossils';
                        }
                        /* Restore original Post Data */
                        wp_reset_postdata();

                        $args = array(
                            'date_query'       => array(
                                                    array(
                                                        'after' => '1 month ago') )
                        );
                        // The Query
                        $user_query = new WP_User_Query( $args );
                        
                        // The Loop
                        if ( ! empty( $user_query->results ) ) {
                            $new_members = $user_query->total_users . ' Members';
                        } else {
                            $new_members = '0 Members';
                        }
                        /* Restore original Post Data */
                        wp_reset_postdata();


                        echo $new_forums.', '.$new_fossils. ', and ' . $new_members . '.';
                        ?>
                        </p>
                    </div>
                </div>
            </div><!-- #atmo-activity-widget -->
            <div id="homepage-whats-new">
                <?php if (is_user_logged_in()): ?>
                <?php bp_get_template_part('activity/post-form'); ?>
                <?php endif; ?>
            </div>
            <div>
                <?php do_action('template_notices'); ?>
            </div>
            <div>
                <div class="clearfix">
                </div>

                <?php do_action('bp_before_directory_activity_list'); ?>

                <div class="activity" role="main">
                    <?php bp_get_template_part('activity/activity-loop'); ?>
                </div><!-- .activity -->

                <?php do_action('bp_after_directory_activity_list'); ?>
                <?php do_action('bp_directory_activity_content'); ?>
                <?php do_action('bp_after_directory_activity_content'); ?>
                <?php do_action('bp_after_directory_activity'); ?>
            </div>
        </div>
    </div>
</div>
