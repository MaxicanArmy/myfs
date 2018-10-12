<?php
namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen;

/**
 * Fossil class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class FossilTest extends myFOSSIL_Specimen_Test {

    public function testSaveGetFossil()
    {
        $taxon = new Specimen\Taxon( null );
        $taxon->common_name = "whale";
        $taxon->name = "whale";
        $this->assertNotNull( $taxon->common_name );
        $taxon->save();
        $taxa = new Specimen\FossilTaxa;
        $location = new Specimen\FossilLocation( null, array(
            'latitude' => 10.2,
            'longitude' => 20.3,
            "city" => "Gainesville",
            "state" => "Florida",
            "county" => "Alachua"
        ) );
        $dimension = new Specimen\FossilDimension( null, array( 'length' => 10,
                'width' => 20, 'height' => 30 ) );
        $reference = new Specimen\Reference( null, array( 'year' => 2014 ) );
        $stratum = new Specimen\Stratum( null, array( 'color' => '#c0c0c0', "name" => "rock" ) );
        $time_interval = new Specimen\TimeInterval( null,
            array( 'early_age' => 20, 'late_age' => 30, "pbdbid" => 15, "name" => "jurrasic" ) );
        $geochronology = new Specimen\FossilGeochronology();

        // Save all our new objects that will comprise our fossil
        foreach ( array( $taxon, $taxa, $location, $dimension, $reference,
                $stratum, $time_interval, $geochronology ) as $obj )
            $this->assertGreaterThan( 0, $obj->save() );

        foreach ( Specimen\FossilTaxa::get_ranks() as $rank ) {
            $this->assertGreaterThan( 0, $taxon->save() );
            $taxa->{ $rank } = $taxon;
            $this->assertNotNull( $taxon->common_name );
            $this->assertGreaterThan( 0, $taxa->save(true) );
            $this->assertNotNull( $taxa->{ $rank }->common_name );
        }

        foreach ( Specimen\FossilGeochronology::get_ranks() as $rank ) {
            $geochronology->{ $rank } = $time_interval;
        }

        // Create new fossil
        $fossil = new Specimen\Fossil(
            null,
            array(
                'taxa_id' => $taxa->save(),
                'location_id' => $location->id,
                'dimension_id' => $dimension->id,
                'reference_id' => $reference->id,
                'stratum_formation_id' => $stratum->id,
                'stratum_member_id' => $stratum->id,
                'stratum_group_id' => $stratum->id,
                'geochronology_id' => $geochronology->save()
            )
        );

        // Save fossil
        $this->assertGreaterThan( 0, $fossil->save() );

        // Assert that everything is in there properly
        foreach ( Specimen\FossilTaxa::get_ranks() as $rank )
            $this->assertEquals( $taxa->{ $rank }->common_name,
            $fossil->taxa->{ $rank }->common_name );
        $this->assertEquals( $location->latitude, $fossil->location->latitude );
        $this->assertEquals( $location->longitude, $fossil->location->longitude );
        $this->assertEquals( $dimension->length, $fossil->dimension->length );
        $this->assertEquals( $dimension->width, $fossil->dimension->width );
        $this->assertEquals( $dimension->height, $fossil->dimension->height );
        $this->assertEquals( $dimension->length, $fossil->dim->length );
        $this->assertEquals( $dimension->width, $fossil->dim->width );
        $this->assertEquals( $dimension->height, $fossil->dim->height );
        $this->assertEquals( $reference->year, $fossil->reference->year );
        $this->assertEquals( $stratum->color, $fossil->strata->formation->color );
        foreach ( Specimen\FossilGeochronology::get_ranks() as $rank ) {
            $this->assertEquals( $time_interval->early_age, $fossil->geochronology->{ $rank }->early_age );
            $this->assertEquals( $time_interval->late_age, $fossil->geochronology->{ $rank }->late_age );
        }

        // Test loading from the database again, also search
        $fossil_id = $fossil->id;
        $fossil = new Specimen\Fossil( $fossil->id );
        foreach ( Specimen\FossilTaxa::get_ranks() as $rank ) {
            $this->assertEquals( $taxa->{ $rank }->common_name,
                $fossil->taxa->{ $rank }->common_name );
            $this->assertNotNull( $taxa->{ $rank }->common_name );
            $this->assertNotNull( $fossil->taxa->{ $rank }->common_name );
            $this->assertTrue( $fossil->taxa->matches_search_query( "whale" ) );
            $this->assertTrue( $fossil->matches_search_query( "whale" ) );
        }
        $this->assertEquals( $location->latitude, $fossil->location->latitude );
        $this->assertEquals( $location->longitude, $fossil->location->longitude );
        $this->assertEquals( $dimension->length, $fossil->dimension->length );
        $this->assertEquals( $dimension->width, $fossil->dimension->width );
        $this->assertEquals( $dimension->height, $fossil->dimension->height );
        $this->assertEquals( $dimension->length, $fossil->dim->length );
        $this->assertEquals( $dimension->width, $fossil->dim->width );
        $this->assertEquals( $dimension->height, $fossil->dim->height );
        $this->assertEquals( $reference->year, $fossil->reference->year );
        $this->assertEquals( $stratum->color, $fossil->strata->formation->color );
        foreach ( Specimen\FossilGeochronology::get_ranks() as $rank ) {
            $this->assertEquals( $time_interval->early_age, $fossil->geochronology->{ $rank }->early_age );
            $this->assertEquals( $time_interval->late_age, $fossil->geochronology->{ $rank }->late_age );
            $this->assertNotNull( $time_interval->name );
            $this->assertTrue( $fossil->matches_search_query( $time_interval->name ) );
        }

        foreach ( array( "gainesvIlL", "fl", "alachua", "Alachua" ) as $location_query ) {
            $this->assertTrue( $fossil->matches_search_query( $location_query ) );
        }

        $this->assertTrue( $fossil->matches_search_query( "rock" ) );
        $this->assertTrue( $fossil->matches_search_query( "jur" ) );
        $this->assertTrue( $fossil->matches_search_query( "jurrasic" ) );
        $this->assertTrue( $fossil->matches_search_query( (string) $fossil->id ) );
    }

}
