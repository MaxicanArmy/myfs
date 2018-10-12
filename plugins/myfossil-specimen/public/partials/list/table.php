<?php

use myFOSSIL\Plugin\Specimen\Fossil;

function myfossil_list_fossils_table( $fossils, $query )
{
    $num_entries = 0;
    $unk = '<span class="unknown">Unknown</span>';
?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Taxon</th>
                    <th>Location</th>
                    <th>Geochronology</th>
                    <th>Lithostratigraphy</th>
                </tr>
            </thead>
            <tbody>
            <?php while ( $fossils->have_posts() ) : $fossils->the_post(); ?>
            <?php
                $fossil = new Fossil( get_the_id() );
                if ( !$query || $fossil->matches_search_query( $query ) ):
                    $num_entries++;
            ?>
                <tr class="hover-hand" data-href="/fossils/<?php echo get_the_id() ?>">
                    <td>
                        <div class="pull-left">
                        <?php
                            $string = $fossil->image;
                            $pattern = '/(\.[A-Za-z]+)$/';
                            $replacement = '-150x150$1';
                        ?>
                            <img class="fossil-list-thumb" src="<?php echo preg_replace($pattern, $replacement, $string); ?>" class="img-responsive" />
                        </div>
                        <div class="fossil-list-caption pull-left">
                            <span class="fossil-name" style="font-weight: bold; font-size: 1.2em; color: #000">
                                <?php echo $fossil->name ?>
                            </span>
                            <p class="author">
                                by <?php echo bp_core_get_userlink( $fossil->author->ID ) ?>
                            </p>
                        </div>
                    </td>
                    <td>
                        <?php echo $fossil->taxa ? $fossil->taxa : $unk ?>
                    </td>
                    <td>
                        <?php echo $fossil->location->wp_post ? $fossil->location : $unk ?>
                    </td>
                    <td>
                        <?php echo $fossil->time_interval? $fossil->time_interval : $unk ?>
                    </td>
                    <td>
                        <?php foreach ( array( 'group', 'formation', 'member' ) as $lith ): ?>
                            <span class="fossil-property"><?php echo ucfirst( $lith ) ?></span>
                            <?php echo ( $fossil->strata && property_exists( $fossil->strata, $lith ) && $fossil->strata->{ $lith } ) ? $fossil->strata->{ $lith } : $unk ?>
                            <br />
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php
    return $num_entries;
}
