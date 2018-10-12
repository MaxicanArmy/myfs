<?php
/**
 * ./tests/SpecimenTest.php
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */

namespace myFOSSIL\Plugin\Specimen\Tests;

/**
 * Base class for UnitTestCase's in our plugin.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 *
 * @since   0.0.1
 */
abstract class myFOSSIL_Specimen_Test extends \WP_UnitTestCase {

    /**
     * Plugin namespace for scope resolution.
     *
     * @since   0.0.1
     * @access  public
     * @var     string  $plugin_namespace
     */
    public $plugin_namespace = '\myFOSSIL\Plugin\Specimen';

    /**
     * Plugin slug (essentially plugin name with underscores).
     *
     * @since   0.0.1
     * @access  public
     * @var     string  $plugin_slug
     */
    public $plugin_slug = 'myFOSSIL_Specimen';

    /**
     * Setup access to the plugin.
     *
     * @since   0.0.1
     * @access  public
     */
    public function setUp()
    {
        parent::setUp();
    }
}
