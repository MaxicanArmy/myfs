<?php
/**
 * ./tests/test-obj-inst.php
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */

namespace myFOSSIL\Plugin\Resources;

/**
 * PluginObjectTest class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class PluginObjectTest extends Tests\myFOSSIL_Resources_Test {


    /**
     *
     */
    function test_myFOSSIL_Resources_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Resources',
            new myFOSSIL_Resources
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Resources_Activator_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace .  '\myFOSSIL_Resources_Activator',
            new myFOSSIL_Resources_Activator
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Resources_Admin_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Resources_Admin',
            new myFOSSIL_Resources_Admin( null, null )
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Resources_Deactivator_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace .  '\myFOSSIL_Resources_Deactivator',
            new myFOSSIL_Resources_Deactivator
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Resources_Loader_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Resources_Loader',
            new myFOSSIL_Resources_Loader
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Resources_Public_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Resources_Public',
            new myFOSSIL_Resources_Public( null, null )
        );
    }


    /**
     *
     */
    function test_myFOSSIL_Resources_i18n_Instantiation()
    {
        $this->assertInstanceOf(
            $this->plugin_namespace . '\myFOSSIL_Resources_i18n',
            new myFOSSIL_Resources_i18n
        );
    }

}
