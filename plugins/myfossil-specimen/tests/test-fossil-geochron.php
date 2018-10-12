<?php

namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen\TimeInterval;
use myFOSSIL\Plugin\Specimen\FossilGeochronology;

/**
 * FossilGeochronology class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class FossilGeochronologyTest extends myFOSSIL_Specimen_Test {

    public function testSaveFossilGeochronology()
    {
        $time_interval = new TimeInterval;
        $fgeo = new FossilGeochronology;
        $fgeo->time_interval_id_era = $time_interval->save();
        $this->assertGreaterThan( 0, $fgeo->save() );
        $this->assertGreaterThan( 0, $fgeo->wp_post->ID );
    }

    public function testGetFossilGeochronology()
    {
        $time_interval = new TimeInterval;
        $time_interval->name = "Test TimeInterval";
        $fgeo = new FossilGeochronology;
        $this->assertGreaterThan( 0, count( $fgeo->_meta_keys ) );
        foreach ( $fgeo->_meta_keys as $k ) {
            $fgeo->{ $k } = $time_interval->save();
            $nfgeo = new FossilGeochronology( $fgeo->save() );
            $this->assertEquals( $fgeo->{ $k }, $nfgeo->{ $k } );
            $this->assertGreaterThan( 0, $fgeo->{ $k } );
            $this->assertGreaterThan( 0, $nfgeo->{ $k } );
            $this->assertInternalType( 'int', $fgeo->{ $k } );
        }

        foreach ( FossilGeochronology::get_ranks() as $k ) {
            $this->assertInstanceOf( 'myFOSSIL\Plugin\Specimen\TimeInterval',
                $fgeo->{ $k } );
        }
    }

    public function testSetFossilGeochronology()
    {
        $fgeo = new FossilGeochronology;
        foreach ( FossilGeochronology::get_ranks() as $k ) {
            $time_interval = new TimeInterval;
            $keys = array( 'level', 'color', 'late_age_ma', 'early_age_ma' );
            foreach ($keys as $k )
                $time_interval->{ $k } = $k;
            $time_interval->save();
            $fgeo->{ $k } = $time_interval;
        }
        $nfgeo = new FossilGeochronology( $fgeo->save() );

        foreach ( $fgeo->_meta_keys as $k ) {
            $fgeo->{ $k } = $time_interval->save();
            $nfgeo = new FossilGeochronology( $fgeo->save() );
            $this->assertEquals( $fgeo->{ $k }, $nfgeo->{ $k } );
            $this->assertGreaterThan( 0, $fgeo->{ $k } );
            $this->assertGreaterThan( 0, $nfgeo->{ $k } );
            $this->assertInternalType( 'int', $fgeo->{ $k } );
        }
        foreach ( FossilGeochronology::get_ranks() as $k ) {
            $this->assertInstanceOf( 'myFOSSIL\Plugin\Specimen\TimeInterval',
                $fgeo->{ $k } );
        }
    }
}
