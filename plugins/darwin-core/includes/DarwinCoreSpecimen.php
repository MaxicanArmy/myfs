<?php
class DarwinCoreSpecimen {
    const POST_TYPE =  'dwc_specimen';

    private $id, $private;

    private $meta_keys = array();

    private $meta = array();

    private $layout = array();

    public function __construct( $post_id=null )
    {
        $this->id = $post_id;

        $meta_values = ( !empty( $post_id ) ) ? get_post_meta( $post_id ) : array();
        
        global $wpdb;
        
        $classes_table_name = $wpdb->prefix . 'darwin_core_classes';
        $terms_table_name = $wpdb->prefix . 'darwin_core_terms';
        $vocabulary_table_name = $wpdb->prefix . 'darwin_core_vocabulary';
        $enabled_terms = $wpdb->get_results( "SELECT " . 
            $classes_table_name . ".className, " .
            $classes_table_name . ".displayName AS 'classDisplayName', " .
            $classes_table_name . ".layout, " .
            $terms_table_name . ".termName, " .
            $terms_table_name . ".displayName, " .
            $terms_table_name . ".valueType FROM " .
            $classes_table_name . " INNER JOIN " . $terms_table_name . " ON " .
            $classes_table_name . ".classID=" . $terms_table_name . ".layoutParent WHERE " .
            $terms_table_name . ".enabled=true ORDER BY " .
            $terms_table_name . ".layoutOrder ASC;", 'ARRAY_A' );

        foreach ($enabled_terms as $current) {
            if (!isset($this->meta_keys[$current['className']])) {
                $this->meta_keys[$current['className']]['displayName'] = $current['classDisplayName'];
            }

            //$current_value = (isset($meta_values[$current['className'].'_'.$current['termName']][0])) ? $meta_values[$current['className'].'_'.$current['termName']][0] : "";
            
            $this->meta_keys[$current['className']]['terms'][$current['termName']] = array('displayName' => $current['displayName'], 'type' => $current['valueType'], 'value' => $meta_values[$current['className'].'_'.$current['termName']][0]);

            $matrix = explode(',', $current['layout']);
            $this->layout[$matrix[0]][$matrix[1]] = $current['className'];
        }

        $this->meta_keys['changelog'] = json_decode($meta_values['changelog'][0], true);
    }

    public function get_schema( ) {
        return $this->meta_keys;
    }

    private function update_meta_values() {
        $meta_values = get_post_meta($this->id);
        
        global $wpdb;
        
        $classes_table_name = $wpdb->prefix . 'darwin_core_classes';
        $terms_table_name = $wpdb->prefix . 'darwin_core_terms';
        $vocabulary_table_name = $wpdb->prefix . 'darwin_core_vocabulary';
        $enabled_terms = $wpdb->get_results( "SELECT " . 
            $classes_table_name . ".className, " .
            $classes_table_name . ".displayName AS 'classDisplayName', " .
            $classes_table_name . ".layout, " .
            $terms_table_name . ".termName, " .
            $terms_table_name . ".displayName, " .
            $terms_table_name . ".valueType FROM " .
            $classes_table_name . " INNER JOIN " . $terms_table_name . " ON " .
            $classes_table_name . ".classID=" . $terms_table_name . ".layoutParent WHERE " .
            $terms_table_name . ".enabled=true ORDER BY " .
            $terms_table_name . ".layoutOrder ASC;", 'ARRAY_A' );

        foreach ($enabled_terms as $current) {
            if (!isset($this->meta_keys[$current['className']])) {
                $this->meta_keys[$current['className']]['displayName'] = $current['classDisplayName'];
            }

            $this->meta_keys[$current['className']]['terms'][$current['termName']] = array('displayName' => $current['displayName'], 'type' => $current['valueType'], 'value' => $meta_values[$current['className'].'_'.$current['termName']][0]);

            $matrix = explode(',', $current['layout']);
            $this->layout[$matrix[0]][$matrix[1]] = $current['className'];
        }

        $this->meta_keys['changelog'] = json_decode($meta_values['changelog'][0], true);
    }

    public function save( $post_values ) {
        $change_log = array();
        $timestamp = time();
        foreach ($this->meta_keys as $className => $classSettings) {
            if ( $className !== 'changelog' ) {
                foreach($classSettings['terms'] as $termName => $termSettings) {
                    $key_name = $className.'_'.$termName;

                    if ($termSettings['value'] !== $post_values[$key_name] && ($termSettings['value'] !== NULL || $post_values[$key_name] !== '')) {
                        $this->meta_keys['changelog'][$timestamp][] = array('key' => $key_name, 'from' => $termSettings['value'], 'to' => $post_values[$key_name]);
                        update_post_meta( $this->id, $key_name, $post_values[$key_name] );
                    }
                }
            }
        }
        update_post_meta( $this->id, 'changelog', json_encode($this->meta_keys['changelog']) );
        update_post_meta( $this->id, 'description', $post_values['description'] );

        $dwc_update = array(
            'ID'            => $this->id,
            'post_status'   => $post_values['post_status']
        );
        wp_update_post( $dwc_update );

        /**
         * a return value was added to support the app, this might cause problems somewhere else in the app (ajax response?). I doubt it but stay frosty
         */
        $this->update_meta_values();
        return $this->id;
    }

    public function bp_group_activity_update( $source='app', $item_id ) {
        global $bp;
        $user_id = get_current_user_id();
        $user_link = bp_core_get_user_domain( $user_id );
        $username =  bp_core_get_user_displayname( $user_id );

        $group = groups_get_group( array( 'group_id' => $item_id ) );
        $group_link = home_url( $bp->groups->slug . '/' . $group->slug );
        $group_name = $group->name;

        $args = array(
            "per_page" => 10,
            "secondary_id" => $this->id,
            "action" => self::POST_TYPE."_created"
        );

        if ( bp_has_activities( $args ) ) {
            bp_the_activity();
            $update_args = array(
                'id' => bp_get_activity_id(),
                'content' => $username." has contributed a new specimen to myFOSSIL!\n[dwc-specimen-created id=".$this->id."]".$this->json_meta_values()."[/dwc-specimen-created]"
            );
        } else {
            $update_args = array(
                'item_id' => ( $source == 'web' ) ? '67' : $item_id,
                'action' => "<a href='{$user_link}'>{$username}</a> posted a new specimen in the group <a href='{$group_link}'>{$group->name}</a> from the myFOSSIL app", //maybe link the word specimen
                'content' => $username." has contributed a new specimen to myFOSSIL test!\n[dwc-specimen-created id=".$this->id."]".$this->json_meta_values()."[/dwc-specimen-created]",
                'secondary_item_id' => $this->id,
                'component' => "groups",
                'type' => self::POST_TYPE."_created",
                'primary_link' => $user_link,
                'user_id' => $user_id,
                'recorded_time' => gmdate('Y-m-d H:i:s')
            );
        }

        $activity_id = bp_activity_add( $update_args );
    }

    public function json_meta() {   
        $return = array();

        for ($count=1;$count<=count($this->layout);$count++) {
            ksort($this->layout[$count]);
            foreach ($this->layout[$count] as $current) {
                $current_object = new stdClass();
                $meta = $this->meta_keys[$current];

                $current_object->class_name = $current;
                $current_object->class_display_name = $meta['displayName'];
                $current_object->terms = array();

                foreach($meta['terms'] AS $termName => $termValues) {
                    $current_terms = new stdClass();
                    $current_terms->term_name = $termName;
                    $current_terms->term_display_name = $termValues['displayName'];
                    $current_terms->value = $termValues['value'];
                    $current_object->terms[] = $current_terms;
                }

                $return[] = $current_object;
            }
        }
        return $return;
    }

    public function json_meta_values() {   
        $return = array();
        $values = array();

        for ($count=1;$count<=count($this->layout);$count++) {
            ksort($this->layout[$count]);
            foreach ($this->layout[$count] as $current) {
                $current_object = new stdClass();
                $meta = $this->meta_keys[$current];

                $current_object->terms = array();

                foreach($meta['terms'] AS $termName => $termValues) {
                    $current_terms = new stdClass();
                    $current_terms->value = $termValues['value'];
                    $current_object->terms[] = $current_terms;

                    if ($termValues['value'] !== null)
                        $values[] = $termValues['value'];
                }

                $return[] = $current_object;
            }
        }
        return json_encode( $values );
    }

    public function display_precise_meta( $class, $depth ) {
        $result = "";
        $count = 0;
        $terms = $this->meta_keys[$class]['terms'];
        
        $terms = apply_filters( 'darwin_core_precise_meta_'.$class, $terms );
        
        $term = array_pop( $terms );

        while ( $term !== null && $count < $depth ) {
            if ( !empty( $term['value'] ) ) {
                $result = '<div><span class="term">'.$term['displayName'].'</span><span class="term-value">'.$term['value'].'</span></div>'.$result;
                $count++;
            }
            $term = array_pop( $terms );
        }
        if ($result === "")
            echo '<div><span class="term-value">Unknown</span></div>';
        else
            echo $result;
    }

    public function display_meta($owner) {
        $access = ($owner) ? 'owner' : 'guest';
        
        for ($count=1;$count<=count($this->layout);$count++) {
            echo '<div class="col-xs-12 col-sm-6">';
            ksort($this->layout[$count]);
            foreach ($this->layout[$count] as $current) {
                $class_output = '';
                $meta_key = $this->meta_keys[$current];
                
                echo '
                    <div>
                        <h3>'.apply_filters('darwin-core-add-'.$access.'-'.$current.'-helper', '').$meta_key['displayName'].'</h3>
                    </div>';

                $output = "";
                foreach($meta_key['terms'] AS $termName => $termValues) {
                    $output .= $this->display_meta_item( $termValues['type'], $current.'_'.$termName, $termValues['displayName'], $termValues['value'], $owner );
                }
                echo apply_filters( 'darwin-core-alter-'.$access.'-'.$current.'-html', $output, $meta_key );
            }
            echo '</div>';
        }
    }
    
    protected function display_meta_item( $type, $key, $displayName, $value, $owner ) {
        $output = ( !empty( $value ) ) ? $value : '';
        $access = ($owner) ? 'owner' : 'guest';
        $html = "";
        switch ( $type ) {
            case 'text' :
                $html .= '<div class="dwc-row"><label class="dwc-label">'.ucwords($displayName).'</label><input class="dwc-input" type="text" placeholder="Unknown" id="'.$key.'" name="'.$key.'" value="'.$output.'"'.((!$owner) ? ' disabled' : '').' />'.apply_filters( 'darwin-core-term-'.$access.'-'.$key.'-html', '' ).'</div>';
                break;
            case 'boolean' :
                $html .= '<div class="dwc-row"><label class="dwc-label">'.ucwords($displayName).'</label><input class="dwc-input" type="checkbox" id="'.$key.'" name="'.$key.'" value="true"'. (($output == 'true') ? ' checked' : '').((!$owner) ? ' disabled' : '').' />'.apply_filters( 'darwin-core-term-'.$access.'-'.$key.'-html', '' ).'</div>';
                break;
        }

        return $html;
    }
}