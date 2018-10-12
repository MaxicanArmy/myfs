<?php
namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen\Stratum;

/**
 * Stratum class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class StratumTest extends myFOSSIL_Specimen_Test {

    public function testSaveStratum()
    {
        $strat = new Stratum;
        $strat->color = "#c0c0c0";
        $this->assertGreaterThan( 0, $strat->save() );
        $this->assertGreaterThan( 0, $strat->wp_post->ID );
    }

    public function testGetStratum()
    {
        $strat = new Stratum;
        $strat->color = "#c0c0c0";
        $new_strat = new Stratum( $strat->save() );
        $this->assertEquals( $strat->color, $new_strat->color );
    }
}
