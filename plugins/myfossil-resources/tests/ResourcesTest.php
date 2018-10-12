<?php
namespace myFOSSIL\Plugin\Resources\Tests;

/**
 * Base class for UnitTestCase's in our plugin.
 *
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 *
 * @since   0.0.1
 */
class myFOSSIL_Resources_Test extends \WP_UnitTestCase {

    /**
     * Plugin namespace for scope resolution.
     *
     * @since   0.0.1
     * @access  public
     * @var     string  $plugin_namespace
     */
    public $plugin_namespace = '\myFOSSIL\Plugin\Resources';

    /**
     * Plugin slug (essentially plugin name with underscores).
     *
     * @since   0.0.1
     * @access  public
     * @var     string  $plugin_slug
     */
    public $plugin_slug = 'myFOSSIL_Resources';

}
