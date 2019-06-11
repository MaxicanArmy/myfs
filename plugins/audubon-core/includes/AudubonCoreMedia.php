<?php
class AudubonCoreMedia {
    const POST_TYPE =  'ac_media';

    private $id, $private;

    private $meta_keys = array();

    private $meta = array();

    private $layout = array();

    public function __construct( $post_id=null )
    {
        $this->id = $post_id;

        $meta_values = get_post_meta($post_id);

        global $wpdb;

        $classes_table_name = $wpdb->prefix . 'audubon_core_classes';
        $terms_table_name = $wpdb->prefix . 'audubon_core_terms';
        $vocabulary_table_name = $wpdb->prefix . 'audubon_core_vocabulary';
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

            $this->meta_keys[$current['className']]['terms'][$current['termName']] = array('displayName' => $current['displayName'], 'type' => $current['valueType'], 'value' => $meta_values[self::POST_TYPE.'_'.$current['className'].'_'.$current['termName']][0]);

            $matrix = explode(',', $current['layout']);
            $this->layout[$matrix[0]][$matrix[1]] = $current['className'];
        }

        $this->meta_keys['changelog'] = json_decode($meta_values['changelog'][0], true);
    }

    public function get_image_src() {
      $args = array(
        'post_parent' => $this->id,
        'post_type' => 'attachment'
      );
      $attachments = get_children($args);
      $media = array_shift( $attachments );
      if ( !is_null( $media ) ) {
        $rval = wp_get_attachment_image_src( $media->ID, '2000')[0];
      }
      return $rval;
    }

    public function save( $post_values ) {
        //$change_log = array();
        $timestamp = time();
        foreach ($this->meta_keys as $className => $classSettings) {
            foreach($classSettings['terms'] as $termName => $termSettings) {
                $key_name = self::POST_TYPE.'_'.$className.'_'.$termName;

                if ($termSettings['value'] !== $post_values[$key_name] && ($termSettings['value'] !== NULL || $post_values[$key_name] !== '')) {
                    $this->meta_keys['changelog'][$timestamp][] = array('key' => $key_name, 'from' => $termSettings['value'], 'to' => htmlspecialchars($post_values[$key_name]));
                    update_post_meta( $this->id, $key_name, htmlspecialchars($post_values[$key_name]) );
                }
            }
        }
        update_post_meta( $this->id, 'changelog', json_encode($this->meta_keys['changelog']) );
        update_post_meta( $this->id, 'description', htmlspecialchars($post_values['description']) );
    }

    public function display_meta($owner) {
        $access = ($owner) ? 'owner' : 'guest';
        for ($count=1;$count<=count($this->layout);$count++) {
            echo "<div class='col-xs-12 col-md-6'>";
            ksort($this->layout[$count]);
            foreach ($this->layout[$count] as $current) {
                $meta_key = $this->meta_keys[$current];

                echo '
                    <div>
                        <h3 class="ac-meta-heading">'.apply_filters('audubon-core-add-'.$access.'-'.$current.'-helper', '').$meta_key['displayName'].'</h3>
                    </div>';

                $output = "";
                foreach($meta_key['terms'] AS $termName => $termValues) {
                    $output .= $this->display_meta_item( $termValues['type'], $current.'_'.$termName, $termValues['displayName'], $termValues['value'], $owner );
                }
                echo apply_filters( 'audubon-core-alter-'.$access.'-'.$current.'-html', $output );
            }
            echo '</div>';
        }
    }

    protected function display_meta_item( $type, $key, $displayName, $value, $owner ) {
        $output = ($value !== '' || $owner) ? $value : 'Unknown';
        $html = "";
        switch ( $type ) {
            case 'text' :
                $html .= '<div class="ac-row"><label class="ac-label">'.ucwords($displayName).'</label><input class="ac-input" type="text" placeholder="'.ucwords($displayName).'" id="'.self::POST_TYPE.'_'.$key.'" name="'.self::POST_TYPE.'_'.$key.'" value="'.$output.'"'.((!$owner) ? ' disabled' : '').' /></div>';
                break;
            case 'boolean' :
                $html .= '<div class="ac-row"><label class="ac-label">'.ucwords($displayName).'</label><input class="ac-input" type="checkbox" id="'.self::POST_TYPE.'_'.$key.'" name="'.self::POST_TYPE.'_'.$key.'" value="true"'. (($output == 'true') ? ' checked' : '').((!$owner) ? ' disabled' : '').' /></div>';
                break;
        }

        return $html;
    }
}
