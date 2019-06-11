<?php
class DarwinCoreSpecimen {
    const POST_TYPE =  'dwc_specimen';

    private $id;

    private $post_obj;

    private $grade = "casual";
    private $curated = "false";
    private $curation_count = 0;

    private $author = 0;

    private $post_status = 'draft';

    private $enabled_terms;

    private $meta_keys = array();

    private $layout = array();

    private $user_access = ''; //admin, owner, or guest

    private $image_assets = array(); //load all of the post id's of associated images

    private $audubon_active = false; //set to true in __construct if audubon core exists


    public function __construct( $post_id=null ) {
      $this->id = $post_id;
      $this->post_obj = get_post($post_id);
      self::set_enabled_terms();

      if (current_user_can('administrator') || current_user_can('dwc_curator'))
        $this->user_access = 'admin';
      elseif (is_user_logged_in() && (get_current_user_id() == get_post_field( 'post_author', $post_id ) || $this->id == null) )
        $this->user_access = 'owner';
      else
        $this->user_access = 'guest';

      //if ( !empty( $this->id ) )
      self::update_meta_values();
    }

    private function update_meta_values() {
        $meta_values = get_post_meta($this->id);

        foreach ($this->enabled_terms as $current) {
          $vocab = null;

          if (!isset($this->meta_keys[$current['className']])) {
              $this->meta_keys[$current['className']]['displayName'] = $current['classDisplayName'];
          }

          if ($current['valueType'] == "select")
            $vocab = self::get_vocabulary($current['termID']);

          $this->meta_keys[$current['className']]['terms'][$current['termName']] = array(
            'displayName' => $current['displayName'],
            'identifier' => $current['identifier'],
            'core' => $current['core'],
            'type' => $current['valueType'],
            'value' => $meta_values[$current['className'].'_'.$current['termName']][0],
            'vocab' => $vocab
          );

          $matrix = explode(',', $current['layout']);
          $this->layout[$matrix[0]][$matrix[1]] = $current['className'];
        }


        $this->author = get_post_field( 'post_author', $this->id );
        $this->post_status = get_post_status( $this->id );
        $this->meta_keys['changelog'] = json_decode($meta_values['changelog'][0], true);
        $this->grade = $meta_values['grade'][0];
        $this->curated = $meta_values['curated'][0];
        $this->curation_count = $meta_values['curation_count'][0];

        $args = array(
    			'post_parent' => $this->id,
    			'post_type' => 'ac_media'
    		);
    		$children = get_children($args);

    		if ( count($children) > 0 )
    			foreach ($children as $ac_media)
            $this->image_assets[] = $ac_media->ID;
    }

    public function get_post_status() {
      return $this->post_status;
    }

    public function get_grade() {
      return $this->grade;
    }

    public function get_curated() {
      return $this->curated;
    }

    public function get_curation_count() {
      return $this->curation_count;
    }

    public function get_image_assets() {
      return $this->image_assets;
    }

    public function get_meta_keys() {
      return $this->meta_keys;
    }

    public function get_layout() {
      return $this->layout;
    }

    public function get_user_access() {
      return $this->user_access;
    }

    public function get_schema( ) {
        return $this->meta_keys;
    }

    public function save( $post_values ) {
        $content = "";
        $change_log = array();
        $timestamp = time();
        foreach ($this->meta_keys as $className => $classSettings) {
            if ( $className !== 'changelog' ) {
                foreach($classSettings['terms'] as $termName => $termSettings) {
                    $key_name = $className.'_'.$termName;
                    if (!empty($post_values[$key_name]))
                      $content .= $post_values[$key_name].";&nbsp;";
                      //$content[] = $post_values[$key_name];

                    if ($termSettings['value'] !== $post_values[$key_name] && ($termSettings['value'] !== NULL || $post_values[$key_name] !== '')) {
                        $this->meta_keys['changelog'][$timestamp][] = array('key' => $key_name, 'from' => $termSettings['value'], 'to' => $post_values[$key_name]);
                        update_post_meta( $this->id, $key_name, $post_values[$key_name] );
                    }
                }
            }
        }
        update_post_meta( $this->id, 'changelog', json_encode($this->meta_keys['changelog']) );

        if ($this->user_access != 'admin')
          update_post_meta( $this->id, 'curated', 'false' );

        $dwc_update = array(
            'ID'            => $this->id,
            'post_content'   => $content
        );
        wp_update_post( $dwc_update );

        $this->update_meta_values();
        return $this->id;
    }

    public function create_content_text() {
        $content = "";
        foreach ($this->meta_keys as $className => $classSettings) {
            if ( $className !== 'changelog' ) {
                foreach($classSettings['terms'] as $termName => $termSettings) {
                    if (!empty($termSettings['value']))
                      $content .= $termSettings['value']."; &nbsp;";
                }
            }
        }
        return $content;
    }

    public function bp_group_activity_update( $item_id=0, $time=null ) { //item_id is the group_id for activity_update in groups
        global $bp;
        $user_id = get_current_user_id();
        $user_link = bp_core_get_user_domain( $user_id );
        $username =  bp_core_get_user_displayname( $user_id );

        if ($item_id > 0) {
          $group = groups_get_group( array( 'group_id' => $item_id ) );
          $group_link = home_url( $bp->groups->slug . '/' . $group->slug );
          $group_name = $group->name;
          $action = "<a href='{$user_link}'>{$username}</a> posted a new specimen in the group <a href='{$group_link}'>{$group->name}</a> from the myFOSSIL app.";
          $component = "groups";
        } else {
          $action = "<a href='{$user_link}'>{$username}</a> posted a new specimen.";
          $component = "activity";
        }

        $args = array(
            "per_page" => 1,
            "secondary_id" => $this->id,
            "action" => self::POST_TYPE."_created"
        );

        if ( bp_has_activities( $args ) ) {
            bp_the_activity();
            $update_args = array(
                'id' => bp_get_activity_id(),
                'content' => $username." has contributed specimen <a href='/dwc-specimen/".$this->id."/'>mFeM ".$this->id."</a> to myFOSSIL!\n[dwc-specimen-created id=".$this->id."]".$this->json_meta_values()."[/dwc-specimen-created]"
            );
        } else {
            $update_args = array(
                'item_id' => $item_id,
                'action' => $action,
                'content' => $username." has contributed specimen <a href='/dwc-specimen/".$this->id."/'>mFeM ".$this->id."</a> to myFOSSIL!\n[dwc-specimen-created id=".$this->id."]".$this->json_meta_values()."[/dwc-specimen-created]",
                'secondary_item_id' => $this->id,
                'component' => $component,
                'type' => self::POST_TYPE."_created",
                'primary_link' => $user_link,
                'user_id' => $user_id,
                'recorded_time' => gmdate('Y-m-d H:i:s')
            );
        }

        if (!empty( $time ) )
          $update_args['recorded_time'] = $time;

        $activity_id = bp_activity_add( $update_args );
    }

    public function bp_group_activity_update_historic( $item_id=0, $time=null, $user_id ) { //item_id is the group_id for activity_update in groups
        global $bp;
        $user_link = bp_core_get_user_domain( $user_id );
        $username =  bp_core_get_user_displayname( $user_id );

        if ($item_id > 0) {
          $group = groups_get_group( array( 'group_id' => $item_id ) );
          $group_link = home_url( $bp->groups->slug . '/' . $group->slug );
          $group_name = $group->name;
          $action = "<a href='{$user_link}'>{$username}</a> posted a new specimen in the group <a href='{$group_link}'>{$group->name}</a> from the myFOSSIL app.";
          $component = "groups";
        } else {
          $action = "<a href='{$user_link}'>{$username}</a> posted a new specimen.";
          $component = "activity";
        }

        $args = array(
            "per_page" => 1,
            "secondary_id" => $this->id,
            "action" => self::POST_TYPE."_created"
        );

        if ( bp_has_activities( $args ) ) {
            bp_the_activity();
            $update_args = array(
                'id' => bp_get_activity_id(),
                'content' => $username." has contributed specimen <a href='/dwc-specimen/".$this->id."/'>mFeM ".$this->id."</a> to myFOSSIL!\n[dwc-specimen-created id=".$this->id."]".$this->json_meta_values()."[/dwc-specimen-created]"
            );
        } else {
            $update_args = array(
                'item_id' => $item_id,
                'action' => $action,
                'content' => $username." has contributed specimen <a href='/dwc-specimen/".$this->id."/'>mFeM ".$this->id."</a> to myFOSSIL!\n[dwc-specimen-created id=".$this->id."]".$this->json_meta_values()."[/dwc-specimen-created]",
                'secondary_item_id' => $this->id,
                'component' => $component,
                'type' => self::POST_TYPE."_created",
                'primary_link' => $user_link,
                'user_id' => $user_id,
                'recorded_time' => gmdate('Y-m-d H:i:s')
            );
        }

        if (!empty( $time ) )
          $update_args['recorded_time'] = $time;

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
                    $current_terms = apply_filters('darwin-core-json-meta-term', $current_terms, $termName, $termValues);
                    if (!empty($current_terms))
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

    public function ingestion_json() {
      $return = new stdClass();

      for ($count=1;$count<=count($this->layout);$count++) {
        ksort($this->layout[$count]);
        foreach ($this->layout[$count] as $current) {
          $meta = $this->meta_keys[$current];

          foreach($meta['terms'] AS $termName => $termValues) {
            $identifier = preg_replace("/^[^\:]+\:/","", $termValues['identifier']);

            if ( $termValues['core'] == true )
              $return->$identifier = $termValues['value'];
          }
        }
      }
      $rRights = 'rightsHolder';
      $rLevel = 'basisOfRecord';
      $rType = 'type';
      $return->$rRights = bp_core_get_user_displayname( $this->post_obj->post_author );
      $return->$rLevel = 'FossilSpecimen';
      $return->specificEpithet = empty($return->specificEpithet) ? null : strtolower($return->specificEpithet);
      $return->infraspecificEpithet = empty($return->infraspecificEpithet) ? null : strtolower($return->infraspecificEpithet);
      $return->$rType = 'PhysicalObject';
      return $return;
    }

    public function ingestion_json_meta() {
      $return = array();

      for ($count=1;$count<=count($this->layout);$count++) {
        ksort($this->layout[$count]);
        foreach ($this->layout[$count] as $current) {
          $meta = $this->meta_keys[$current];

          foreach($meta['terms'] AS $termName => $termValues) {
            $rTerm = new stdClass();
            $rTerm->term = $termName;
            $rTerm->data_type = 'string';
            $rTerm->url = 'https://dwc.tdwg.org/terms/#'.$termValues['identifier'];

            if ( $termValues['core'] == true )
              $return[] = $rTerm;
          }
        }
      }
      $rRightsHolder = new stdClass();
      $rRightsHolder->term = 'rightsHolder';
      $rRightsHolder->data_type = 'string';
      $rRightsHolder->url = 'https://dwc.tdwg.org/terms/#dcterms:rightsHolder';
      $return[] = $rRightsHolder;
      $rBasisOfRecord = new stdClass();
      $rBasisOfRecord->term = 'basisOfRecord';
      $rBasisOfRecord->data_type = 'string';
      $rBasisOfRecord->url = 'https://dwc.tdwg.org/terms/#dwc:basisOfRecord';
      $return[] = $rBasisOfRecord;
      $rType = new stdClass();
      $rType->term = 'type';
      $rType->data_type = 'string';
      $rType->url = 'https://dwc.tdwg.org/terms/#dcterms:type';
      $return[] = $rType;
      return $return;
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

    public function display_meta() {
      $images = false;

      for ($count=1;$count<=count($this->layout);$count++) {
        echo '<div class="col-xs-12 col-sm-6">';
        if (!$images && $count == 2) {
          $images = true;
					if (class_exists('AudubonCoreMedia')) {
						audubon_core::display_dwc_associated_media($this->id);
					}

					if ($this->user_access != 'guest') : ?>
					<div class="ac-create-btn-wrapper">
						<?php wp_nonce_field('upload_media_for_dwc_specimen', 'upload_media_for_dwc_specimen_nonce'); ?>
						<input type="hidden" id="upload_media_action" name="upload_media_action" value="upload_media_for_dwc_specimen" />
						<div id="dwc-dragndrop"><p>Drag &amp; Drop Media Here</p></div>
						<div>
							<?php wp_nonce_field('upload_media_url_for_dwc_specimen', 'upload_media_url_for_dwc_specimen_nonce'); ?>
					        <input id="dwc-media-url" class="dwc-input" type="text" value="" placeholder="Or enter URL" name="dwc-media_url" />
							<a id="dwc-upload-media-url" href="#" class="btn btn-primary ajax-btn">Save URL &rarr;</a>
						</div>
					</div>
				<?php endif;
        }

        ksort($this->layout[$count]);
        foreach ($this->layout[$count] as $current) {
          $class_output = '';
          $meta_key = $this->meta_keys[$current];

          echo '
            <div>
              <h3>'.apply_filters('darwin-core-add-'.$current.'-helper', '', $this->user_access ).$meta_key['displayName'].'</h3>
            </div>';

          $output = "";
          foreach($meta_key['terms'] AS $termName => $termValues) {
            $output .= $this->display_meta_item( $termValues['type'], $current, $termName, $termValues['displayName'], $termValues['value'] );
          }
          echo apply_filters( 'darwin-core-alter-'.$current.'-html', $output, $meta_key, $this->user_access );
        }
        echo '</div>';
      }
    }
    public function get_image_previews_html() {
      $html = "";
  		$args = array(
  			'post_parent' => $this->id,
  			'post_type' => 'ac_media'
  		);
  		$children = get_children($args);

  		if ( count($children) > 0 ) {
  			foreach ($children as $ac_media) {
          $context = "thumb";
          $size = '320';
          $thumb_id = get_post_meta( $ac_media->ID, 'thumb_id', true );
      		$media_url = get_post_meta( $ac_media->ID, 'resource_url', true );
      		$media_ext = get_post_meta( $ac_media->ID, 'resource_ext', true );

          if ($media_ext == 'stl') {
            if (!empty($thumb_id)) {
              $html .= "<img src='" . wp_get_attachment_image_src( $thumb_id, 'thumbnail' )[0] . "' class='stl-thumbnail' />";
            } else {
              $html .= "<img src='".plugin_dir_path( realpath( __FILE__ ) )."stl-no-thumb.png' class='stl-thumbnail' />";
            }
          } else {
            $args = array(
              'post_parent' => $ac_media->ID,
              'post_type' => 'attachment'
            );
            $attachments = get_children($args);
            $media = array_shift( $attachments );
            if ( !is_null( $media ) ) {
              $html .= wp_get_attachment_image( $media->ID, 'thumbnail', false, array('class'=>'wp-image-'.$media->ID) );
            }
          }
  			}
      }

      return $html;
    }

    public function display_meta_item( $type, $class, $term, $displayName, $value, $summary=false) {
      $key = $class."_".$term;
      $locked = $summary ? true : ($this->grade == "research" && $this->user_access != 'admin' ? true : ($this->user_access == 'guest' ? true : false));
      $output = !empty( $value ) ? $value : ($locked ? 'UNKNOWN' : '');
      $html = "";
      switch ( $type ) {
          case 'text' :
            if ($locked)
              $html .= '<div class="dwc-row"><label class="dwc-label">'.ucwords($displayName).'</label><span class="dwc-input'.($output == "UNKNOWN" ? ' dwc-unknown' : '').'" placeholder="UNKNOWN" id="'.$key.($summary ? '-summary' : '').'" >'.$output.'</span></div>';
            else
              $html .= '<div class="dwc-row"><label class="dwc-label">'.ucwords($displayName).'</label><input class="dwc-input" type="text" placeholder="UNKNOWN" id="'.$key.'" name="'.$key.'" value="'.$output.'"'.(($this->user_access == 'guest') ? ' disabled' : '').' />'.apply_filters( 'darwin-core-term-'.$key.'-helper', '', $this->user_access ).'</div>';
            break;
          case 'boolean' :
            if ($locked)
              $html .= '<div class="dwc-row"><label class="dwc-label">'.ucwords($displayName).'</label><span class="dwc-input'.($output == "UNKNOWN" ? ' dwc-unknown' : '').'" id="'.$key.($summary ? '-summary' : '').'" >'.$output.'</span></div>';
            else
              $html .= '<div class="dwc-row"><label class="dwc-label">'.ucwords($displayName).'</label><input class="dwc-input" type="checkbox" id="'.$key.'" name="'.$key.'" value="true"'. (($output == 'true') ? ' checked' : '').(($this->user_access == 'guest') ? ' disabled' : '').' />'.apply_filters( 'darwin-core-term-'.$key.'-helper', '', $this->user_access ).'</div>';
              break;
          case 'select' :
            if ($locked) {
              $html .= '<div class="dwc-row"><label class="dwc-label">'.ucwords($displayName).'</label><span class="dwc-input'.($output == "UNKNOWN" ? ' dwc-unknown' : '').'" id="'.$key.($summary ? '-summary' : '').'" >'.$output.'</span></div>';
            } else {
              $temp_terms = $this->meta_keys[$class]['terms'];
              $vocab = $temp_terms[$term]['vocab'];

              $html .= '<div class="dwc-row"><label class="dwc-label">'.ucwords($displayName).'</label><select class="dwc-input" id="'.$key.'" name="'.$key.'" /><option value="">UNKNOWN</option>';
              foreach ($vocab as $v) {
                $html .= '<option value="'.$v["displayVocab"].'" '.($v["displayVocab"] == $output ? 'selected' : '').'>'.$v["displayVocab"].'</option>';
              }
              $html .= '</select>'.apply_filters( 'darwin-core-term-'.$key.'-helper', '', $this->user_access ).'</div>';
            }
            break;

      }

      return apply_filters( 'dwc-specimen-edit-term-html', $html, $type, $key, $displayName, $value, $this->user_access, $summary );
    }

    protected function set_enabled_terms() {
      global $wpdb;

      $classes_table_name = $wpdb->prefix . 'darwin_core_classes';
      $terms_table_name = $wpdb->prefix . 'darwin_core_terms';
      $vocabulary_table_name = $wpdb->prefix . 'darwin_core_vocabulary';
      $this->enabled_terms = $wpdb->get_results( "SELECT " .
        $classes_table_name . ".className, " .
        $classes_table_name . ".displayName AS 'classDisplayName', " .
        $classes_table_name . ".layout, " .
        $terms_table_name . ".termID, " .
        $terms_table_name . ".termName, " .
        $terms_table_name . ".displayName, " .
        $terms_table_name . ".identifier, " .
        $terms_table_name . ".core, " .
        $terms_table_name . ".valueType FROM " .
        $classes_table_name . " INNER JOIN " . $terms_table_name . " ON " .
        $classes_table_name . ".classID=" . $terms_table_name . ".layoutParent WHERE " .
        $terms_table_name . ".enabled=true ORDER BY " .
        $terms_table_name . ".layoutOrder ASC;", 'ARRAY_A' );
    }

    protected function get_vocabulary( $termID ) {
      global $wpdb;

      $vocabulary_table_name = $wpdb->prefix . 'darwin_core_vocabulary';
      $rval = $wpdb->get_results( "SELECT " .
      $vocabulary_table_name . ".displayVocab FROM " .
        $vocabulary_table_name . " WHERE " .
        $vocabulary_table_name . ".termID=" . $termID . ";", 'ARRAY_A' );

      return $rval;
    }
/*
    public function generate_specimen_page() {
      $class_tabs = array();
      $tab_content = array();
      $summary_content = '';
      $hasimages = '';
      $hassummary = '';
      $images = false;

      if (!empty($this->id)) {
        $previews = $this->get_image_previews_html();
        $summary_content .= audubon_core::get_image_summary_html($this->id);
        if (!empty($previews)) {
          $hasimages = ' hascontent';
          $hassummary = ' hascontent';
        }
      }

      $class_tabs['Images'] = "<li class='dwc-specimen-nav-Images active".$hasimages."'><a href='#' data-target='dwc-specimen-content-Images'>Images</a></li>";
      ?>
      <div id="darwin-core">
        <div id="dwc-specimen-header">
          <div class="container">
            <?php if ($this->user_access != 'guest') : ?>
            <?php if ($this->user_access == 'admin') : ?>
            <form>
              <?php wp_nonce_field('research_grade_dwc_specimen', 'research_grade_specimen_nonce'); ?>
              <a id="dwc-research-grade-specimen" href="#" class="btn btn-info ajax-btn" style="float:right;<?php echo (empty($this->id)) ? 'display:none;' : ''; ?>">Mark Research Grade</a>
            </form>
            <?php endif; ?>
            <form>
              <?php wp_nonce_field('delete_dwc_specimen', 'delete_dwc_specimen_nonce'); ?>
              <a id="dwc-delete-specimen" href="#" class="btn btn-danger ajax-btn" style="float:right;clear:right;<?php echo (empty($this->id)) ? 'display:none;' : ''; ?>">Delete Specimen</a>
            </form>
            <?php endif; ?>
            <div><span id="dwc-specimen-header-id"><?php echo (empty($this->id)) ? '' : 'SPECIMEN '.$this->id; ?></span></div>
            <div id="dwc-specimen-header-author"><?php echo (empty($this->id)) ? '' : 'Author: '.bp_core_get_user_displayname( $this->author ); ?></div>
            <div id="dwc-specimen-header-last-updated"><?php echo (empty($this->id)) ? '' : 'Updated '.get_post_modified_time('m-d-Y H:i', false, $this->id); ?></div>
          </div>
        </div>
      	<div class="container single">
      <?php
      for ($count=1;$count<=count($this->layout);$count++) {
        */
        /*
        $summary_content .= '<div class="col-xs-12 col-sm-6">';
        if (!$images && $count == 2 && !empty($this->id)) {
          $images = true;
					if (class_exists('AudubonCoreMedia')) {
  				  ob_start();
						audubon_core::display_dwc_associated_media($this->id);
    				$summary_content .= ob_get_clean();
					}
        }
        */
        /*
        ksort($this->layout[$count]);
        foreach ($this->layout[$count] as $current) {
          $hascontent = '';
          $class_output = '';
          $meta_key = $this->meta_keys[$current];
          $tab_content[$current]['header'] = '<div class="dwc-specimen-content-'.$current.'"><div><h3>'.
            apply_filters('darwin-core-add-'.$current.'-helper', '', $this->user_access).$meta_key['displayName'].'</h3></div>';
          $summary_content .= '<div><h3>'.$meta_key['displayName'].'</h3></div>';

          foreach($meta_key['terms'] AS $termName => $termValues) {
            if (!empty($termValues['value'])) {
              $hascontent = ' hascontent';
              $hassummary = ' hascontent';
            }
            $tab_content[$current]['class'] = $current;
            $tab_content[$current]['terms'] .= $this->display_meta_item( $termValues['type'], $current.'_'.$termName, $termValues['displayName'], $termValues['value']);
            $summary_content .= $this->display_meta_item( $termValues['type'], $current.'_'.$termName, $termValues['displayName'], $termValues['value'], true);
          }
          $tab_content[$current]['footer'] = "</div>";

          $class_tabs[] = "<li class='dwc-specimen-nav-".$current.$hascontent."'><a href='#' data-target='dwc-specimen-content-".$current."'>".$meta_key['displayName']."</a></li>";
        }
        $summary_content .= '</div>';
      }
      $class_tabs['Summary'] = "<li class='dwc-specimen-nav-Summary".$hassummary."'><a href='#' data-target='dwc-specimen-content-Summary'>Summary</a></li>";

      echo "<ul id='dwc-specimen-nav'>";
      foreach ($class_tabs as $tab)
        echo $tab;
      echo "</ul><div class='clear'></div>";
      ?>
      <div class='dwc-specimen-content-Images'>
        <p>The only requirement for starting an entry is an image (or 3d image) of the specimen. You can upload files of type .jpg|.png|.gif or .stl (3d images).</p>
        <p>If you are already hosting the file on another site (for example dropbox) you can provide the URL instead of uploading. Please be aware that the URL must be a direct hotlink to the media or it may not display properly later.</p>
        <div id='dwc-specimen-image-preview'><?php echo $previews; ?></div>
        <div id='dwc-dragndrop'><p>Upload Images</p></div>
        <form>
          <?php wp_nonce_field('dwc_specimen_upload_media', 'dwc_specimen_upload_media_nonce'); ?>
          <input type='hidden' id='upload_media_action' name='upload_media_action' value='dwc_specimen_upload_media' />
        </form>
      </div>
			<form method="post" id="dwc-specimen-form" action="#">
        <?php wp_nonce_field('dwc_specimen_upload_terms', 'dwc_specimen_upload_terms_nonce'); ?>
				<input type="hidden" id="dwc_specimen_id" name="dwc_specimen_id" value="<?php echo $this->id; ?>" />
        <input type="hidden" name="action" value="dwc_specimen_upload_terms" />
        <?php
        foreach ($tab_content as $key => $value)
          echo $value['header'].apply_filters( 'darwin-core-alter-'.$value['class'].'-html', $value['terms'], $this->meta_keys[$value['class']], $this->user_access).$value['footer'];
        echo "</form>";
        echo "<div class='dwc-specimen-content-Summary'>".$summary_content."</div>";
        echo "</div></div>";
    }
*/
}
