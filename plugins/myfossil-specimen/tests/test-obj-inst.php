<?php
/**
 * ./tests/test-obj-inst.php
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */

namespace myFOSSIL\Plugin\Specimen;

/**
 * PluginObjectTest class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class PluginObjectTest extends Tests\myFOSSIL_Specimen_Test {


    /**
     *
     */
    function test_myFOSSIL_Specimen_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Specimen',
            new myFOSSIL_Specimen
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Specimen_Activator_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace .  '\myFOSSIL_Specimen_Activator',
            new myFOSSIL_Specimen_Activator
        );

        $activator = new myFOSSIL_Specimen_Activator;
        $activator->activate();
    }


    /**
     *
     */
    function test_myFOSSIL_Specimen_Admin_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Specimen_Admin',
            new myFOSSIL_Specimen_Admin( null, null )
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Specimen_Deactivator_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace .  '\myFOSSIL_Specimen_Deactivator',
            new myFOSSIL_Specimen_Deactivator
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Specimen_Loader_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Specimen_Loader',
            new myFOSSIL_Specimen_Loader
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Specimen_Public_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Specimen_Public',
            new myFOSSIL_Specimen_Public( null, null )
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Specimen_i18n_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Specimen_i18n',
            new myFOSSIL_Specimen_i18n
        );
    }

}
