<?php
use myFOSSIL\Plugin\Specimen\Fossil;

$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

$fossil_search_query = array_key_exists("fossil_search", $_REQUEST)
    && $_REQUEST["fossil_search"] ? $_REQUEST["fossil_search"] : null;

	
if ( bp_displayed_user_id() ) {
	
	$wp_query_args = array(
	        'post_type' => Fossil::POST_TYPE,
	        'posts_per_page' => -1,
	        'paged' => $paged,
	    );
}
else {
	
	$wp_query_args = array(
	        'post_type' => Fossil::POST_TYPE,
	        'posts_per_page' => $fossil_search_query ? -1 : 10,
	        'paged' => $paged,
	    );	
	
}

if ( bp_displayed_user_id() ) {
    $wp_query_args['author'] = bp_displayed_user_id();
    if ( bp_displayed_user_id() == bp_loggedin_user_id() )
        $wp_query_args['post_status'] = 'any';
}

$fossils = new WP_Query( $wp_query_args );

?>

<?php if ( ! bp_displayed_user_id() ): ?>
<div id="buddypress-header">

    <div id="item-header" role="complementary" class="container">

        <div class="row" id="groups-header">

            <div id="item-header-content" class="col-md-9">
                <h1>Fossils</h1>

                <?php do_action( 'template_notices' ); ?>

            </div><!-- #item-header-content -->

			
            <div class="col-sm-12 col-md-3">
                <?php myfossil_fossil_create_button() ?>
            </div>
        </div>

    </div>

    <div id="item-nav" class="container">
        <div class="item-list-tabs" role="navigation">
            <ul class="nav nav-tabs">
                <li class="selected current active">
                    <a>
                        <?php printf( __( 'All Fossils', 'buddypress' ) ); ?>
                    </a>
                </li>
            </ul>
        </div><!-- .item-list-tabs -->
    </div>
</div>
<?php endif; ?>

<?php if ( ! bp_displayed_user_id() ): ?>
<div id="buddypress" class="container page-styling no-border-top">
<?php endif; ?>



          <div class="row" style="margin-bottom: 10px">
          
		<?php if (  bp_is_my_profile() ) : ?>
          
			<div class="col-sm-12 col-md-6 col-md-offset-6 text-right">
			  <div style="margin-bottom: 5px">
					<?php myfossil_fossil_create_button(); ?>  
			  </div>
			</div>
		   
		<?php endif ?>
          
            <div class="col-sm-12 col-md-6 col-md-offset-6 text-right">
              
                <form action="" method="get" class="form-inline">
                    <div class="form-group">
                        <label class="sr-only" for="fossils_search">Search Fossils</label>
                        <input
                            type="text"
                            name="fossil_search"
                            id="fossils_search"
                            class="form-control input-sm"
                            value="<?=$fossil_search_query ?>"
                            placeholder="By name, species, etc."
                        />
                    </div>
                   

                       <button type="submit" class="btn btn-primary btn-search"  ><span class="fa-stack">                        
                                <i class="fa fa-search fa-stack-1x fa-inverse"></i>
                            </span>Search fossils</button>
                </form>
            </div>
        </div>

    <main id="main" class="site-main" role="main">
        <?php $num_entries = myfossil_list_fossils_table( $fossils, $fossil_search_query ); ?>
        <?php
        $fossil_of = ($fossil_search_query == null) ? $fossils->found_posts : $num_entries;
        $fossil_from = ($fossils->query['posts_per_page'] * ($fossils->query['paged'] - 1) + 1);
        $fossil_to = ($fossils->query['posts_per_page'] * $fossils->query['paged'] > $fossil_of || !empty($fossil_search_query) || $fossils->query['posts_per_page'] == -1) ? $fossil_of : $fossils->query['posts_per_page'] * $fossils->query['paged'];
        ?>
        <div class="row-centered"><p>Viewing <?php echo $fossil_from.' - '. $fossil_to.' of '.$fossil_of; ?> public fossils</p></div>
        <div class="row-centered">
            <?=myfossil_paginate_links( $fossils ) ?>
        </div>
    </main><!-- #main -->

<?php if ( ! bp_displayed_user_id() ): ?>
</div><!-- #primary -->
<?php endif; ?>

<script type="text/javascript">
jQuery(document).ready(function($) { 
	if (window.location.search.indexOf('mfs=1') > -1)
		jQuery('#fossils_search').focus(); 
});
</script>	

<?php
wp_reset_query();
