<?php do_action( 'bp_before_profile_loop_content' ); ?>

<?php if ( bp_has_profile() ) : ?>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-9">
    <?php while ( bp_profile_groups() ) : bp_the_profile_group(); ?>

        <?php if ( bp_profile_group_has_fields() ) : ?>

            <?php do_action( 'bp_before_profile_field_content' ); ?>

            <div class="bp-widget <?php bp_the_profile_group_slug(); ?>">

                <h5 class="side-header">General Information</h5>

                <table class="table" style="border: 0">
                    <tr class="sr-only">
                        <th>Key</th>
                        <th>Value</th>
                    </tr>

                    <?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

                        <?php if ( bp_field_has_data() ) : ?>

                            <tr<?php bp_field_css_class(); ?>>

                                <td><?php bp_the_profile_field_name(); ?></td>

                                <td class="data"><?php bp_the_profile_field_value(); ?></td>

                            </tr>

                        <?php endif; ?>

                        <?php do_action( 'bp_profile_field_item' ); ?>

                    <?php endwhile; ?>

                </table>

                <?php
                $args = array('field' => 25, 'user_id' => bp_displayed_user_id());
                $consent = bp_get_profile_field_data($args);
                
                if ( bp_is_my_profile() ) {
                    if ($consent == 'No') {
                        echo '<p>You currently do not consent to participate in the FOSSIL study.<p>';
                    } else {
                        echo '<p>You currently consent to participate in the FOSSIL study.<p>';
                    }
                    echo '<p>To view the Description and Informed Consent information, or to change your decision to participate click <a href="/description-and-informed-consent/">here</a>.';
                }
                ?>
            </div>

            <?php do_action( 'bp_after_profile_field_content' ); ?>

        <?php endif; ?>

    <?php endwhile; ?>

    <?php do_action( 'bp_profile_field_buttons' ); ?>
        </div>
        <div id="right-side" class="col-xs-12 col-sm-12 col-md-4 col-lg-3">
            <?php
            /*
             * Display some of the User's friends, if they have any
             */
            bp_get_template_part( 'members/single/partials/members' );

            /*
             * Display some of the User's groups that they belong to, if any
             */
            bp_get_template_part( 'members/single/partials/groups' );

            /*
             * Display some of the User's resources, if they have any
             */
            bp_get_template_part( 'members/single/partials/resources' );

            ?>
        </div>
    </div>
<?php endif; ?>

<?php do_action( 'bp_after_profile_loop_content' ); ?>
