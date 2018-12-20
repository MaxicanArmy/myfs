<div id="logged-out-homepage">
    <div id="hero-image">
        <div>
            <h1>Social Paleontology</h1>
            <p>We're building a community of amateur and professional paleontologists.<br />
            <a id="hero-image-register" href="/register/"><img src="<?php bloginfo('stylesheet_directory'); ?>/static/img/join-2x.png" /></a><br />
            <a id="hero-image-login" href="<?=wp_login_url( get_permalink() ); ?>">Already a member of myFOSSIL? Login</a>
            </p>
        </div>
    </div>
    <div id="latest-content">
        <div id="latest-content-header">
            <p>New content in the last 30 days</p>
        </div>
        <div id="latest-content-wrapper">
            <div class="latest-content-item">
                <div class="latest-content-number"><p><?= myfs_core_recent_specimen_count(30); ?></p></div>
                <div class="latest-content-type"><p><img src="<?php bloginfo('stylesheet_directory'); ?>/static/img/latest-content-fossils-2x.png" /><br />FOSSILS</p></div>
            </div>
            <div class="latest-content-item">
                <div class="latest-content-number"><p><?= myfs_core_recent_forum_post_count(30); ?></p></div>
                <div class="latest-content-type"><p><img src="<?php bloginfo('stylesheet_directory'); ?>/static/img/latest-content-posts-2x.png" /><br />POSTS</p></div>
            </div>
            <div class="latest-content-item">
                <div class="latest-content-number"><p><?= myfs_core_recent_member_count(30); ?></p></div>
                <div class="latest-content-type"><p><img src="<?php bloginfo('stylesheet_directory'); ?>/static/img/latest-content-members-2x.png" /><br />MEMBERS</p></div>
            </div>
        </div>
    </div>
    <div id="mission-statement">
        <h5>F.O.S.S.I.L &ndash; Fostering Opportunities for Synergistic STEM with Informal Learners</h5>
        <p>Connect with professional paleontologists, students, amateur/avocational paleontologists, educators, and more in our online paleontological community. Join interest groups and start learning together.</p>
    </div>
    <div id="featured-fossil">
        <div id="featured-fossil-header">
            <h5>Improve Your Collection</h5>
            <p>Record and maintain information about your fossils. Use the myFOSSIL database to begin curating your collection. Get help with identification and classification.</p>
        </div>


        <?php
        use myFOSSIL\Plugin\Specimen\Fossil;
        global $post;
        $selection = get_option( 'atmo_selectors' )["fossil_id"];

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
        <div id="featured-fossil-content" style="background-image:url('<?=$string;?>');">
            <div id="featured-fossil-details">
                <h5><a href="/fossils/<?php echo $fossil->ID; ?>/"><?php echo $fossil->name; ?></a></h5>
                <p>Contributed by <?php echo $fossil->author->display_name; ?><br /><?php echo $fossil_date; ?></p>
            </div>
        </div>
    </div>
    <div id="featured-forum">
        <div id="featured-forum-header">
            <h5>Join Important Conversations</h5>
            <p>Collaborate and share information with other members of the community! Discuss ideas with others in the forums, post a status update, or comment on fossil images in the gallery.</p>
        </div>
        <div id="featured-forum-content">
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
            <h4 id="featured-forum-forum"><?= $forum; ?></h4>
            <div id="featured-forum-details">
                <div id="featured-forum-author">
                    <p class="nicename"><a href="/members/<?= get_userdata($author)->user_nicename; ?>/"><?= get_avatar( $author, 100 ); ?><br />@<?= get_userdata($author)->user_nicename; ?></a></p>
                </div>
                <div id="featured-forum-topic">
                    <p class="topic-title"><?=$title;?></p>
                </div>
            </div>
            <?php

            $args = array(
                'posts_per_page'    => -1,
                'post_parent'       => get_option( 'atmo_selectors' )["forum_id"],
                'post_type'         => array( 'reply' )
            );
            // The Query
            $the_query = new WP_Query( $args );

            echo '<div id="featured-forum-replies">';
            $unique_users = array($author);
            while ( $the_query->have_posts() && (count($unique_users) < 4)) {
                $the_query->the_post();
                if (!in_array($post->post_author, $unique_users)) {
                    echo '<div><p><a href="/members/' . get_userdata($post->post_author)->user_nicename . '/">' . get_avatar( $post->post_author, 75 ) . '<br />@' .  get_userdata($post->post_author)->user_nicename . '</a></p></div>';
                    array_push($unique_users, $post->post_author);
                }
            }
            
            echo '</div>';
            /* Restore original Post Data */
            wp_reset_postdata();
            ?>
        </div>
    </div>
<div class="clearfix"></div>
</div>