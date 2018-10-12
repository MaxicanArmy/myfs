<?php
namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen\Reference;

/**
 * Reference class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class ReferenceTest extends myFOSSIL_Specimen_Test {

    public function testSaveReference()
    {
        $ref = new Reference;
        $ref->year = 2004;
        $this->assertGreaterThan( 0, $ref->save() );
        $this->assertGreaterThan( 0, $ref->wp_post->ID );
    }

    public function testGetReference()
    {
        $ref = new Reference;
        $ref->year = 2004;
        $new_ref = new Reference( $ref->save() );
        $this->assertEquals( $ref->year, $new_ref->year );
    }
}
