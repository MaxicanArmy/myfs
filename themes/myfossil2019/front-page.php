<?php 
use myFOSSIL\Plugin\Specimen\Fossil;
get_header();
?>

<?php if (is_user_logged_in()) :
    do_action('bp_before_directory_activity');
    include get_template_directory()."/homepage-logged-in.php";
?>
<?php else : ?>
    <div class="container landingpage">
        <div class="call-to-action">
            <h2>Social Paleontology</h2>
            <p>We're building a community of amateur and professional paleontologists.</p>
            <p><a class="join-us" href="/register/">Join Us!</a></p>
            <p class="little-login"><a href="<?=wp_login_url( get_permalink() ); ?>">Already a member of myFOSSIL? Login</a><br />F.O.S.S.I.L &ndash; Fostering Opportunities for Synergistic STEM with Informal Learners</p>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-12 hidden-md hidden-lg sm-heading">
                <h3>Grow your network!</h3>
            </div>
        </div>
        <div class="row widget-wrap">
            <div id="growth-widget" class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <p class="title">New content in the last 30 days</p>
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
                    $new_forums = $the_query->found_posts;
                } else {
                    $new_forums = 0;
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
                    $new_fossils = $the_query->found_posts;
                } else {
                    $new_fossils = 0;
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
                    $new_members = $user_query->total_users;
                } else {
                    $new_members = 0;
                }
                /* Restore original Post Data */
                wp_reset_postdata();


                echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4"><p><a href="/fossils/"><span class="growth-num">' . $new_fossils . '</span><br /><img src="/wp-content/uploads/2016/09/fossils-stack.png" class="img-responsive" /></a></p></div>';
                echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4"><p><a href="/forums/"><span class="growth-num">' . $new_forums . '</span><br /><img src="/wp-content/uploads/2016/09/posts-stack.png" class="img-responsive" /></a></p></div>';
                echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4"><p><a href="/members/"><span class="growth-num">' . $new_members . '</span><br /><img src="/wp-content/uploads/2016/09/members-stack.png" class="img-responsive" /></a></p></div>';
                echo '<div class="clearfix"></div>';
                ?>
            </div>
            <div class="hidden-xs hidden-sm col-md-6 col-lg-6 widget-exp">
                <h3>Grow your network!</h3>
                <p>Connect with professional paleontologists, students, amateur/avocational paleontologists, educators, and more in our online paleontological community.  Join interest groups and start learning together.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-12 hidden-md hidden-lg sm-heading">
                <h3>Join Important Conversations</h3>
            </div>
        </div>
        <div class="row widget-wrap">
            <div class="hidden-xs hidden-sm col-md-6 col-lg-6 widget-exp">
                <h3>Join Important Conversations</h3>
                <p>Collaborate and share information with other members of the community!  Discuss ideas with others in the forums, post a status update, or comment on fossil images in the gallery.</p>
            </div>
            <div id="forum-widget" class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <?php
                    global $post;
                    $args = array(
                        'p'             => get_option( 'atmo_forum_selector' )["id_number"],
                        'post_type'     => array( 'topic' )
                    );
                    // The Query
                    $the_query = new WP_Query( $args );

                    if ( $the_query->have_posts() ) {
                        $the_query->the_post(); 
                        $title = '<a href="'. $post->guid . '">' . $post->post_title . '</a>';
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
                        $forum =  '<a href="'. $post->guid . '">' . $post->post_title . ' Forum</a>';
                    }
                    
                    /* Restore original Post Data */
                    wp_reset_postdata();
                ?>
                <p class="title"><?php echo $forum; ?></p>
                <div class="row topic-post">
                <?php
                    echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3"><p class="nicename"><a href="/members/' . get_userdata($author)->user_nicename . '/">' . get_avatar( $author, 75 ) . '<br />@' . get_userdata($author)->user_nicename . '</a></p></div>';
                    echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9"><p class="topic-title">'.$title.'</p></div></div>';

                    $args = array(
                        'posts_per_page'    => -1,
                        'post_parent'       => get_option( 'atmo_forum_selector' )["id_number"],
                        'post_type'         => array( 'reply' )
                    );
                    // The Query
                    $the_query = new WP_Query( $args );

                    echo '<div class="row last-posters">';
                    $unique_users = array($author);
                    while ( $the_query->have_posts() && (count($unique_users) < 5)) {
                        $the_query->the_post();
                        if (!in_array($post->post_author, $unique_users)) {
                            echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3"><p class="nicename"><a href="/members/' . get_userdata($post->post_author)->user_nicename . '/">' . get_avatar( $post->post_author, 50 ) . '<br />@' .  get_userdata($post->post_author)->user_nicename . '</a></p></div>';
                            array_push($unique_users, $post->post_author);
                        }
                    }
                    
                    echo '</div><div class="clearfix"></div>';
                    /* Restore original Post Data */
                    wp_reset_postdata();
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-12 hidden-md hidden-lg sm-heading">
                <h3>Enhance Your Collection</h3>
            </div>
        </div>
        <div class="row widget-wrap">
            <div id="fossil-widget" class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <?php
                    global $post;
                    $selection = get_option( 'atmo_fossil_selector' )["id_number"];

                    if (is_integer($selection) && $selection > 0) {
                        $args = array(
                            'p'             => $selection,
                            'post_type'     => array( 'myfossil_fossil' )
                        );
                    }
                    else {
                        $args = array(
                            'posts_per_page'    => 1,
                            'orderby'          => 'date',
                            'order'            => 'DESC',
                            'post_type'        => array( 'myfossil_fossil' ),
                            'post_status'      => 'publish'
                        );
                    }
                    // The Query
                    $the_query = new WP_Query( $args );

                    if ( $the_query->have_posts() ) {
                        $the_query->the_post(); 
                        $fossil = new Fossil( $post->ID );
                    }
                    
                    $fossil_date = date( "F d, Y", strtotime( $post->post_date) );
                    $string = $fossil->image;
                    $pattern = '/(\.[A-Za-z]+)$/';
                    $replacement = '-150x150$1';

                    /* Restore original Post Data */
                    wp_reset_postdata();
                ?>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"><p><a href="/fossils/<?php echo $fossil->ID; ?>/"><img class="img-responsive" src="<?php echo preg_replace($pattern, $replacement, $string); ?>" /></a></p></div>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"><p class="title"><a href="/fossils/<?php echo $fossil->ID; ?>/"><?php echo $fossil->name; ?></a></p><p>Contributed by <?php echo bp_core_get_userlink( $fossil->author->ID ); ?></p><p><?php echo $fossil_date; ?></p></div>
            </div>
            <div class="hidden-xs hidden-sm col-md-6 col-lg-6 widget-exp">
                <h3>Improve Your Collection</h3>
                <p>Record and maintain information about your fossils.  Use the myFOSSIL database to begin curating your collection.  Get help with identification and classification.</p>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php get_footer(); ?>
