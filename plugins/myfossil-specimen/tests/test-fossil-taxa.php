<?php

namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen\Taxon;
use myFOSSIL\Plugin\Specimen\FossilTaxa;

/**
 * FossilTaxa class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class FossilTaxaTest extends myFOSSIL_Specimen_Test {

    public function testSaveFossilTaxa()
    {
        $taxon = new Taxon;
        $taxa = new FossilTaxa;
        $taxa->taxon_id_class = $taxon->save();
        $this->assertGreaterThan( 0, $taxa->save() );
        $this->assertGreaterThan( 0, $taxa->wp_post->ID );
    }

    public function testGetFossilTaxa()
    {
        $taxon = new Taxon;
        $taxon->name = "Test Taxon";
        $taxa = new FossilTaxa;
        $this->assertGreaterThan( 0, count( $taxa->_meta_keys ) );
        foreach ( $taxa->_meta_keys as $k ) {
            $taxa->{ $k } = $taxon->save();
            $ntaxa = new FossilTaxa( $taxa->save() );
            $this->assertEquals( $taxa->{ $k }, $ntaxa->{ $k } );
            $this->assertGreaterThan( 0, $taxa->{ $k } );
            $this->assertGreaterThan( 0, $ntaxa->{ $k } );
            $this->assertInternalType( 'int', $taxa->{ $k } );
        }

        foreach ( FossilTaxa::get_ranks() as $k ) {
            $this->assertInstanceOf( 'myFOSSIL\Plugin\Specimen\Taxon',
                $taxa->{ $k } );
        }
    }

    public function testSetFossilTaxa()
    {
        $taxa = new FossilTaxa;
        foreach ( FossilTaxa::get_ranks() as $k ) {
            $taxon = new Taxon;
            $taxon->name = $k;
            $taxon->common_name = $k;
            $taxon->rank = $k;
            $taxon->save();
            $taxa->{ $k } = $taxon;
        }
        $ntaxa = new FossilTaxa( $taxa->save() );

        foreach ( $taxa->_meta_keys as $k ) {
            $taxa->{ $k } = $taxon->save();
            $ntaxa = new FossilTaxa( $taxa->save() );
            $this->assertEquals( $taxa->{ $k }, $ntaxa->{ $k } );
            $this->assertGreaterThan( 0, $taxa->{ $k } );
            $this->assertGreaterThan( 0, $ntaxa->{ $k } );
            $this->assertInternalType( 'int', $taxa->{ $k } );
        }
        foreach ( FossilTaxa::get_ranks() as $k ) {
            $this->assertInstanceOf( 'myFOSSIL\Plugin\Specimen\Taxon',
                $taxa->{ $k } );
        }
    }
}
