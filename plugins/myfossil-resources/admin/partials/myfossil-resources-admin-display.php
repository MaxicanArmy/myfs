<?php
namespace myFOSSIL\Plugin\Resources\Admin;

/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://github.com/myfossil
 * @since      0.0.1
 *
 * @package    myFOSSIL_Resources
 * @subpackage myFOSSIL_Resources/admin/partials
 */

function admin_settings_page() { 
    ?>
    <div class="wrap">
        <h2>myFOSSIL Resources</h2>
        <div id="message"></div>
        <p>Populate Places with default data loaded from this plugin's CSV file.</p>
        <a class="button" id="load">Load default data</a>
    </div>
    <?php 
} 
