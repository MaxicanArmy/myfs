<div id="buddypress">
    <div id="homepage" class="container">
      <?php get_search_form(); ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">
                <div class="feature-highlight">
                    <h4>Featured<br />Fossil</h4>
                    <?php
                        global $post;
                        $selection = get_option( 'atmo_selectors' )["fossil_id"];

                        if (is_integer($selection) && $selection > 0) {
                            $args = array(
                                'p'             => $selection,
                                'post_type'     => array( 'dwc_specimen' )
                            );
                        }
                        else {
                            $args = array(
                                'posts_per_page'    => 1,
                                'orderby'          => 'date',
                                'order'            => 'DESC',
                                'post_type'        => array( 'dwc_specimen' ),
                                'post_status'      => 'publish'
                            );
                        }
                        // The Query
                        $the_query = new WP_Query( $args );

                        if ( $the_query->have_posts() ) {
                            $the_query->the_post();
                            $fossil = new DarwinCoreSpecimen( $post->ID );
                        }

                        $audubon = new AudubonCoreMedia($fossil->get_image_assets()[0]);

                        $fossil_date = date( "F d, Y", strtotime( $post->post_date) );
                        $string = $audubon->get_image_src();
                        $pattern = '/(\.[A-Za-z]+)$/';
                        $replacement = '-150x150$1';
                    ?>
                    <div style="padding-top:10px;text-align:center;">
                        <a href="/dwc-specimen/<?php echo $post->ID; ?>/">
                            <img style="margin:auto;" class="img-responsive" src="<?php echo preg_replace($pattern, $replacement, $string); ?>" />
                        </a>
                        <p style="margin-top:10px;"><a href="/dwc-specimen/<?php echo $post->ID; ?>/"><?php echo "SPECIMEN ".$post->ID; ?></a></p>
                        <p style="color:#FFFFFF;font-size:9pt;">Contributed by<br /><?php echo bp_core_get_user_displayname( $post->post_author ); ?><br /><br />
                        <?php echo $fossil_date; ?><br /><br />
                        <a href="<?php echo bp_core_get_userlink( $post->post_author, false, true ); ?>">@<?php echo bp_core_get_user_displayname( $post->post_author ); ?></a><br /><br />
                        <a href="/dwc-specimen/" id="fossil-create-new">ADD YOURS</a>
                        </p>
                    </div>
                    <?php
                    /* Restore original Post Data */
                    wp_reset_postdata();
                    ?>
                </div>
                <div id="sidebar-social-logos">
                    <p>
                        <a href="https://twitter.com/projectFOSSIL" target="_blank" rel="nofollow">
                            <img src="<?=get_template_directory_uri() ?>/static/img/logos/twitter.png" />
                        </a>
                        <a href="https://www.facebook.com/TheFossilProject/" target="_blank" rel="nofollow">
                            <img src="<?=get_template_directory_uri() ?>/static/img/logos/facebook.png" />
                        </a>
                        <a href="https://www.youtube.com/channel/UCt18MbS9hR6BjGK6yV_aI_A" target="_blank" rel="nofollow">
                            <img src="<?=get_template_directory_uri() ?>/static/img/logos/youtube.png" />
                        </a>
                        <a href="https://www.instagram.com/thefossilproject/" target="_blank" rel="nofollow">
                            <img src="<?=get_template_directory_uri() ?>/static/img/logos/insta.png" />
                        </a>
                    </p>
                </div>
                <div class="upcoming-events">
                    <h4>Upcoming Events</h4>
                    <?php echo do_shortcode("[tribe_events_list]"); ?>
                </div>
                <div class="forum-word-cloud">
                    <?php echo do_shortcode("[bbp-topic-tags]"); ?>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <?php echo do_shortcode("[huge_it_slider id='".get_option( 'atmo_selectors' )["slider_id"]."']"); ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 recent-activity-widget">
                        <h4>Latest Forum Posts</h4>
                        <div class="featured-forum-post" style="padding:0 50px 0 50px;">
                            <?php
                                global $post;
                                $args = array(
                                    'p'             => get_option( 'atmo_selectors' )["forum_id"],
                                    'post_type'     => array( 'topic' )
                                );
                                // The Query
                                $the_query = new WP_Query( $args );

                                if ( $the_query->have_posts() ) {
                                    $the_query->the_post();
                                    $title = '<a href="'. $post->guid . '" style="font-size:9pt;">' . $post->post_title . '</a>';
                                    $parent = $post->post_parent;
                                    $author = $post->post_author;
                                }

                                /* Restore original Post Data */
                                wp_reset_postdata();

                                $args = array(
                                    'p'             => $parent,
                                    'post_type'     => array( 'forum' )
                                );
                                // The Query
                                $the_query = new WP_Query( $args );

                                if ( $the_query->have_posts() ) {
                                    $the_query->the_post();
                                    $forum =  '<a href="'. $post->guid . '" style="color:#9B9B9B;font-size:9pt;">' . $post->post_title . ' Forum</a>';
                                }

                                /* Restore original Post Data */
                                wp_reset_postdata();
                            ?>
                            <div class="topic-post">
                            <?php
                                echo '<div class="author-avatar"><a href="/members/' . get_userdata($author)->user_nicename . '/">' . get_avatar( $author, 60 ) . '<br />' . get_userdata($author)->display_name . '</a></div>';
                                echo '<div style="text-align:center;overflow:auto;"><h4>Featured Forum Post</h4><p class="title">'.$forum.'</p><p class="topic-title">'.$title.'</p></div></div>';
                            ?>
                        </div>
                        <?php
                        $reply_args = array(
                            'posts_per_page'   => 30,
                            'orderby'          => 'date',
                            'order'            => 'DESC',
                            'post_type'        => array( 'reply' ),
                            'post_status'      => 'publish'
                        );
                        // The Query
                        $reply_query = new WP_Query( $reply_args );

                        // The Loop
                        if ( $reply_query->have_posts() ) {
                            echo '<ul class="recent-forum-list">';
                            while ( $reply_query->have_posts() ) : ?>

                                <li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>" style="list-style: none;">
                                    <?php $reply_query->the_post();?>
                                    <div style="float:left;padding:10px 10px 0 10px;">
                                        <?php echo bp_core_fetch_avatar( 'item_id='.$post->post_author.'&type=full&width=60&height=60' ) ?>
                                    </div>
                                    <div style="overflow:auto;">
                                        <div class="activity-heading">
                                            <a class="recent-forum-poster" href="<?php echo bp_core_get_user_domain( $post->post_author ) ?>" title="<?php echo bp_core_get_user_displayname( $post->post_author ) ?>"><?php echo bp_core_get_user_displayname( $post->post_author ) ?></a> posted: <br />
                                            <?php
                                                echo '<span class="recent-forum-content">';
                                                if (strlen(strip_tags($post->post_content)) > 120) {
                                                    echo substr(strip_tags($post->post_content), 0, 120) . '...';
                                                } else {
                                                    echo substr($post->post_content, 0, strlen($post->post_content) );
                                                }
                                                echo '</span><br />in forum:<br />';
                                                $post = get_post( $post->post_parent );

                                                echo '<a href="' . bbp_get_topic_permalink() . '">' . get_the_title() . '</a><br />';
                                                $reply_query->reset_postdata();
                                            ?>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
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
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 recent-forum-widget">
                        <h4>myFOSSIL on Twitter</h4>
                        <div style="height:375px;overflow:hidden;overflow-y: scroll;">
                            <a class="twitter-timeline" href="https://twitter.com/projectFOSSIL/lists/fossil-news?ref_src=twsrc%5Etfw">A Twitter List by projectFOSSIL</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 newest-members-widget">
                        <h4>Newest Members</h4>
                        <?php if ( bp_has_members( 'per_page=100&type=newest' ) ) :
                            do_action( 'bp_before_directory_members_list' );
                            echo '<div class="newest-members-list">';
                            while ( bp_members() ) : bp_the_member();
                                if (current_user_has_avatar(bp_get_member_user_id())) : ?>
                                <div class="activity-entry" style="background-color:transparent;border:none;">
                                    <div class="newest-members-avatar"><a href="<?php bp_member_permalink(); ?>" title="<?php bp_member_name(); ?>"><?php bp_member_avatar( 'type=full&width=75&height=75' ); ?></a></div>
                                    <div class="newest-members-details"><p><a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a></p><p><?php bp_member_profile_data(array('field' => 'Location')); ?></p></div>
                                </div>
                                <?php endif; ?>
                            <?php
                            endwhile;
                            echo '</div>'; ?>

                            <?php do_action( 'bp_after_directory_members_list' ); ?>

                            <?php bp_member_hidden_fields(); ?>

                        <?php else: ?>
                        <div id="message" class="info">
                            <p><?php _e( "Sorry, no members were found.", 'buddypress' ); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="homepage-whats-new-mask">
                    <div style="float:left;padding-right:10px">
                	   <?php echo bp_get_loggedin_user_avatar('type=full&width=50&height=50')."&nbsp;&nbsp;&nbsp;What's new, ".bp_get_user_firstname()."?"; ?>
                    </div>
                	<div style="overflow:hidden;">
                        <textarea rows="1" id="show-homepage-whats-new" style="width:100%"></textarea>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div id="homepage-whats-new" style="display:none;">
                <?php bp_get_template_part('activity/post-form'); ?>
                </div>
                <div>
                <?php do_action('template_notices'); ?>
                </div>
                <div>
                    <div class="clearfix"></div>
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
    </div><!-- .container -->
</div><!-- #buddypress -->
