<?php
namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen\FossilLocation;

/**
 * FossilLocation class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class FossilLocationTest extends myFOSSIL_Specimen_Test {

    /**
     *
     *
     * @covers FossilLocation::save
     * @covers FossilLocation::__set
     * @covers FossilLocation::__get
     */
    public function testSaveFossilLocation()
    {
        $loc = new FossilLocation;
        $loc->latitude = 10.0;
        $loc->longitude = 20.0;
        $this->assertGreaterThan( 0, $loc->save() );
        $this->assertGreaterThan( 0, $loc->wp_post->ID );
    }

    /**
     *
     *
     * @covers FossilLocation::save
     * @covers FossilLocation::__set
     * @covers FossilLocation::__get
     */
    public function testGetFossilLocation()
    {
        $loc = new FossilLocation;
        $loc->latitude = 10.0;
        $loc->longitude = 20.0;
        $new_loc = new FossilLocation( $loc->save() );

        $this->assertEquals( $loc->latitude, $new_loc->latitude );
        $this->assertEquals( $loc->longitude, $new_loc->longitude );
    }

    /**
     *
     *
     * @covers FossilLocation::register_cpt
     */
    public function testDimensionRegisterCpt()
    {
        $this->assertNotInstanceOf( 'WP_Error', FossilLocation::register_cpt() );
    }
}
