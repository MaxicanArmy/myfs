<?php
/**
 * ./includes/class-myfossil-specimen-deactivator.php
 *
 * Fired during plugin deactivation
 *
 * @subpackage  myFOSSIL/includes
 *
 * @link        https://github.com/myfossil
 * @since       0.0.1
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 */


namespace myFOSSIL\Plugin\Specimen;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's
 * deactivation.
 *
 * @author     Brandon Wood <bwood@atmoapps.com>
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 */
class myFOSSIL_Specimen_Deactivator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    0.0.1
     */
    public static function deactivate()
    {
        /*
        $models = array(
                new Taxon,
                new Location,
                new GeologicalTimeScale,
                new GeologicalTimeInterval,
                new FossilOccurence
            );

        foreach ( $models as $model )
            $model->deactivate( true );
        */
    }
}
