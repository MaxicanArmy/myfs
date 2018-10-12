<?php
namespace myFOSSIL\Plugin\Specimen\Tests;

use myFOSSIL\Plugin\Specimen\TimeInterval;

/**
 * TimeInterval class.
 *
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/tests
 */
class TimeIntervalTest extends myFOSSIL_Specimen_Test {

    public function testSaveTimeInterval()
    {
        $ti = new TimeInterval;
        $ti->early_age = 100;
        $this->assertGreaterThan( 0, $ti->save() );
        $this->assertGreaterThan( 0, $ti->wp_post->ID );
    }

    public function testGetTimeInterval()
    {
        $ti = new TimeInterval;
        $ti->early_age = 100;
        $new_ti = new TimeInterval( $ti->save() );
        $this->assertEquals( $ti->early_age, $new_ti->early_age );
    }
}
