<?php

function fossil_header( $fossil=null, $view='main' )
{
?>

    <div id="buddypress-header" class="dark">
        <div id="item-header" class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-2">
                    <a href="<?php echo $fossil->image ?>">
                        <img class="avatar img-responsive fossil-image" src="<?php echo $fossil->image ?>" />
                    </a>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-8">
                    <h1>Fossil <?php echo sprintf( "%06d", $fossil->id ) ?></h1>
                    <input type="hidden" id="post_id" value="<?php echo $fossil->id ?>" />
                    <input type="hidden" id="myfossil_specimen_nonce"
                            value="<?php echo wp_create_nonce( 'myfossil_specimen' ) ?>" />
                    <dl class="inline fossil-header">
                        <dt>Author</dt>
                        <dd>
                            <a href="<?php echo bp_core_get_user_domain( $fossil->author->ID ) ?>">
                                <?php echo trim( $fossil->author->display_name ) ?>
                            </a>
                        </dd>
                        <dt>Updated</dt><dd><?php echo $fossil->updated_at ?></dd>
                        <?php if ( $fossil->location && $fossil->location->country ): ?>
                            <dt>Location</dt><dd><?php echo $fossil->location->country ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>

        <div id="item-nav" class="container">
            <ul class="nav nav-tabs">
                <li class="<?php echo ( $view == 'information' ) ? "active" : null ?>">
                    <a href="/fossils/<?php echo $fossil->id ?>/">Information</a>
                </li>
                <li class="<?php echo ( $view == 'images' ) ? "active" : null ?>">
                    <a href="/fossils/<?php echo $fossil->id ?>/images">Images</a>
                </li>
                <li class="<?php echo ( $view == 'history' ) ? "active" : null ?>">
                    <a href="/fossils/<?php echo $fossil->id ?>/history">History</a>
                </li>
                <?php if ( current_user_can( 'edit_post', $fossil->ID ) ) : ?>
                <li class="<?php echo ( $view == 'settings' ) ? "active" : null ?>">
                    <a href="/fossils/<?php echo $fossil->id ?>/settings">Settings</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <?php
}
