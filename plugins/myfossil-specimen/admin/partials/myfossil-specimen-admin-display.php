<?php
/**
 * ./admin/partials/myfossil-specimen-admin-display.php
 *
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/myfossil
 * @since      0.0.1
 *
 * @package    myFOSSIL
 * @subpackage myFOSSIL/admin/partials
 */

namespace myFOSSIL\Plugin\Specimen;

function admin_tools_page()
{
?>

    <div class="wrap">
        <h2>myFOSSIL Specimen</h2>
        <div id="message"></div>
        <?php wp_nonce_field( 'myfossil_nonce', 'myfossil_nonce' ); ?>
        <p>Populate Taxonomies with default data</p>
        <a class="button" id="load-taxonomies">Load WordPress Taxonomies</a>

        <p>Populate Time Intervals with default data</p>
        <a class="button" id="load-geochronology">Load Geochronology</a>
    </div>

    <?php
}
