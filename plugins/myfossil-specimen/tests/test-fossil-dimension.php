<?php
namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen\FossilDimension;

/**
 * FossilDimension class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class FossilDimensionTest extends myFOSSIL_Specimen_Test {

    public $fossil_dimension;

    public function setUp()
    {
        parent::setUp();
        $this->fossil_dimension = new FossilDimension(
            null,
            array(
                'length_meters' => 0.100,
                'width_meters'  => 0.200,
                'height_meters' => 0.300,
            )
        );
        $this->fossil_dimension->save();
    }

    /**
     *
     *
     * @covers  FossilDimension::save
     */
    public function testSaveDimension()
    {
        $fossil_dimension = new FossilDimension(
            null,
            array(
                'length_meters' => 0.100,
                'width_meters'  => 0.200,
                'height_meters' => 0.300,
            )
        );

        $fossil_dimension->save();
        $this->assertGreaterThan( 0, $fossil_dimension->save() );
        $this->assertGreaterThan( 0, $fossil_dimension->wp_post->ID );
    }

    /**
     *
     *
     * @covers  FossilDimension::__get
     */
    public function testGetDimension()
    {
        $dimension = new FossilDimension( $this->fossil_dimension->wp_post->ID );
        $this->assertEquals( $dimension->length, $this->fossil_dimension->length );
        $this->assertEquals( $dimension->width, $this->fossil_dimension->width );
        $this->assertEquals( $dimension->height, $this->fossil_dimension->height );
    }

    /**
     *
     *
     * @covers FossilDimension::__set
     */
    public function testSetDimension()
    {
        $dimension = new FossilDimension( $this->fossil_dimension->wp_post->ID );

        $length = $dimension->length;
        $width = $dimension->width;
        $height = $dimension->height;

        $this->assertEquals( $length, $dimension->length );
        $this->assertEquals( $width, $dimension->width );
        $this->assertEquals( $height, $dimension->height );

        $dimension->length = $length * 2;
        $dimension->width = $width * 2;
        $dimension->height = $height * 2;

        $this->assertEquals( $length * 2, $dimension->length );
        $this->assertEquals( $width * 2, $dimension->width );
        $this->assertEquals( $height * 2, $dimension->height );
    }

    /**
     *
     *
     * @covers FossilDimension::bp_format_activity
     */
    public function testDimBpFrmtActivity()
    {
    }

    /**
     *
     *
     * @covers FossilDimension::register_cpt
     */
    public function testDimensionRegisterCpt()
    {
        $this->assertNotInstanceOf( 'WP_Error', FossilDimension::register_cpt() );
    }

    /**
     *
     *
     * @covers  FossilDimension::as_cm
     */
    public function testGetDimensionAsCm()
    {
        $dimension = new FossilDimension( $this->fossil_dimension->wp_post->ID );
        $this->assertEquals( $dimension->length * 100, $dimension->as_cm( 'length' ) );
        $this->assertEquals( $dimension->width * 100, $dimension->as_cm( 'width' ) );
        $this->assertEquals( $dimension->height * 100, $dimension->as_cm( 'height' ) );
    }

    /**
     *
     *
     * @covers  FossilDimension::__toString
     */
    public function testDimensionAsString()
    {
        $this->assertTrue( is_string( sprintf( '%s', $this->fossil_dimension ) ) );
    }
}
