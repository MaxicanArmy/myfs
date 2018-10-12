<?php
namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen\Taxon;

/**
 * Taxon class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class TaxonTest extends myFOSSIL_Specimen_Test {

    public function testSaveTaxon()
    {
        $taxon = new Taxon;
        $taxon->common_name = 'whale';
        $this->assertGreaterThan( 0, $taxon->save() );
        $this->assertGreaterThan( 0, $taxon->wp_post->ID );
    }

    public function testGetTaxon()
    {
        $taxon = new Taxon;
        $taxon->common_name = 'whale';
        $new_taxon = new Taxon( $taxon->save() );
        $this->assertEquals( $taxon->common_name, $new_taxon->common_name );
    }
}
