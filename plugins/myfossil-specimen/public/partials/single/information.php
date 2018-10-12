<?php

require_once 'information/images.php';
require_once 'information/comments.php';
require_once 'information/dimensions.php';
require_once 'information/geochronology.php';
require_once 'information/lithostratigraphy.php';
require_once 'information/location.php';
require_once 'information/taxonomy.php';

function myfossil_fossil_render_single_information( $fossil )
{
?>
    <div id="buddypress" class="container page-styling site-main" role="main">
        <?php if ( current_user_can( 'edit_post', $fossil->id ) ) {?>
           <?php if ( get_post_status( $fossil->id ) == 'draft' ) { ?>
                <div class="alert alert-info" role="alert">
                   <p class="alert-content"> This specimen is currently <strong>unpublished</strong> and visible only to you.</p>
            <?php } else { ?>
                <div class="alert alert-success" role="alert">
                  <p class="alert-content">  This specimen is currently <strong>published</strong> and visible to the public. </p>
            <?php } ?>
                    <br />
                    <br />
                    <div class="row">
                    <div class="col-xs-8 col-lg-8">
                        <?php $draft = ( get_post_status( $fossil->id ) == 'draft' ); ?>
                        <div class="btn-group" data-toggle="buttons">
                            <label class="btn btn-default btn-sm <?php echo ( $draft ) ? "btn-active active" : null ?>">
                                <input type="radio" name="status"
                                    id="draft" autcomplete="off"
                                    <?php echo ( $draft ) ? "checked" : null ?>
                                    class="post_status" value="draft" />
                                <i class="fa fa-fw fa-eye-slash"></i>
                                Private
                            </label>
                            <label class="btn btn-default btn-sm <?php echo ( $draft ) ? null : "btn-active active" ?>">
                                <input type="radio" name="status"
                                    id="published" autcomplete="off"
                                    <?php echo ( $draft ) ? null : "checked" ?>
                                    class="post_status" value="publish" />
                                <i class="fa fa-fw fa-eye"></i>
                                Public
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-4 col-lg-4">
                      <a class="pull-right" href="settings">
                        <i class="fa fa-fw fa-trash-o"></i>
                        Delete
                      </a>
                    </div>
            </div>
        </div>

         <div class="col-xs-12">
            <div class="alert alert-danger" role="alert">You must click the "save" button in each section in order to save progress.  The save button will appear when you edit a section.</div>
        </div>
        <?php } ?>

        <div class="row">
           

            <!-- Classification -->
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-8">
                <?php fossil_taxonomy( $fossil ); ?>
                <?php fossil_dimensions( $fossil ); ?>
            </div>

            <!-- Image(s) -->
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
                <?php fossil_images( $fossil ); ?>
            </div>

        </div>


        <div class="row">

            <!-- Location -->
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <?php fossil_location( $fossil ); ?>
            </div>

        </div>


        <div class="row">

            <!-- Geochronology -->
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <?php fossil_geochronology( $fossil ); ?>
            </div>

            <!-- Lithostratigraphy -->
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <?php fossil_lithostratigraphy( $fossil ); ?>
            </div>

        </div>


        <div>
            <?php if ( comments_open() || '0' != get_comments_number() ): ?>
            <div class="row">

                <!-- Comments -->
                <div class="col-lg-12">
                    <?php fossil_comments( $fossil ); ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php
}
