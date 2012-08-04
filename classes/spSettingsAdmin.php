<?php

require_once 'spSettings.php';

class spSettingsAdmin extends spSettings {

    public $info;
    public $hooks;

    function printAdminPage($data){
        global $_POST;
        global $_GET;
        if( !empty($_POST) ) $this->saveSettings();
        $this->addInfo();
        
        echo '<form action="admin.php?page=';
        echo empty($_GET['page'])?'':$_GET['page'];
        echo '&subpage=';
        echo empty($_GET['subpage'])?'':$_GET['subpage'];
        echo '" method="post">';
        echo '<table class="form-table">';

        foreach ($data as $key=>$value) {
            $type = $value[0];
            $name = $value[1];
            switch ($type) {
                case 'title':      $this->printHeader( $name );     break;
                case 'file':       $this->printFile( $name );       break;
                case 'option':     $this->printOptions( $name );    break;
                case 'text':       $this->printText( $name );       break;
                case 'checkboxes': $this->printCheckboxes( $name ); break;
                case 'strings':    $this->printStrings( $name );    break;
            }
        }

        $this->printSave();
        
        echo '</table></form>';
    }

    function saveSettings(){

        global $_POST;

        foreach ($_POST as $key=>$value) {
            if( isSet( $this->settingsData[$key] ) ){
                switch ($this->settingsData[$key]->type) {
                    case 'option': $this->settingsData[$key]->value = $value; break;
                    case 'strings':
                        if( is_array($value) ){
                            $v = $value;
                            foreach ($v as $v_key=>$v_value) {
                                $v_value = trim($v_value);
                                if( empty($v_value) or in_array( $v_value, $this->settingsData[$key]->mandatory) ){
                                    unset( $v[$v_key] );
                                }else{
                                    $v[$v_key] = trim($v_value);
                                }
                            }
                        }else{
                            $v = array( );
                        }
                        
                        $v = array_merge($this->settingsData[$key]->mandatory, $v);
                        $this->settingsData[$key]->value = $v;
                        break;
                    case 'checking': if( ! isSet( $_POST[ $value ] ) ){ $this->settingsData[ $value ]->value = array(); } break;
                    case 'checkbox':
                        if( is_array($value) ){
                            $v = array();
                            foreach ($value as $v_key=>$v_value) {
                                $v_value = trim($v_value);
                                if( in_array( $v_value, $this->settingsData[$key]->possible ) ){
                                    $v[] = $v_value;
                                }
                            }
                        }else{
                            $v = array( );
                        }
                        $this->settingsData[$key]->value = $v;
                        break;
                    case 'text': update_option( $key, stripslashes ( $value ) ); break;
                    case 'file': file_put_contents( $this->settingsData[$key]->path, stripslashes($value) ) ; break;
                }
            }
        }
        
        if ( $fp = @fopen($this->settingsPath, "wt") ) {
            foreach ($this->settingsData as $key=>$data) {
                if( 'checking' == $data->type) continue;
                if( 'text' == $data->type) continue;
                fwrite($fp, "$key:");
                if( is_array($data->value) ){
                    fwrite($fp, implode(',', $data->value ) );
                    update_option( $key, implode(',', $data->value ) );
                }else{
                    fwrite($fp, trim( trim($data->value) ) );
                    update_option( $key, $data->value );
                }
                fwrite($fp, "\n");
            }
            echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>'._('Settings saved.').'</strong></p></div>';
        }else{
            echo '<h1>FATAL ERROR: Cannot write settings into "'.$this->settingsPath.'" file </h1>';
        }

        $this->getNiceSettings();

        $this->addHooks();

        $adminHooks = spClasses::get('Hooks');

        foreach ($_POST as $key=>$value) {
            if( isSet( $this->settingsData[$key] ) ){
                if( isSet( $this->hooks[$key] ) ){
                    $adminHooks->add($this->hooks[$key]);
                }
            }
        }
        $adminHooks->run();
    }
    
    function addHooks(){
        if( !empty($this->hooks) ){ return $this->hooks; }

        return $this->hooks = array(

            // SEO > Main SEO Files
            'sp_file_edit_htaccess_enable' =>    array('updateHtaccess'),
            'sp_file_robots_txt_enable' =>       array('updateSitemaps'),
            'sp_file_root_sitemap_xml_enable' => array('updateSitemaps'),
            'sp_add_rss_to_robots_sitemap' =>    array('updateSitemaps'),
            'sp_add_atom_to_robots_sitemap' =>   array('updateSitemaps'),

            // Speed > HTML Cache
            'sp_cache_level' => array('updateHtaccess'),
            'sp_cache_reaction_on_comments' => array('deleteCache'),
            'sp_cache_reaction_on_session' => array('deleteCache'),
            'sp_cache_disabled_substrings' => array('deleteCache'),

            // Speed > Compression
            'sp_mod_gzip_ext_check' => array('updateHtaccess','deleteCache'),
            'sp_mod_minify_check' => array('updateHtaccess','deleteCache'),

            // Speed > Browser Cache
            'sp_mod_expires_ext' => array('updateHtaccess'),
            'sp_mod_expires_time' => array('updateHtaccess'),

            // Speed > Fast 404
            'sp_fast404' => array('updateHtaccess'),

            // Security > Main settings
            // Security > RFI Blocks

            // Security > Minimal Antispam
            'sp_enable_miniantispam' => array('deleteCache'),
            'sp_enable_miniantispam_referer' => array(),

            // Security > Censorship
            'sp_disable_bad_words' => array(),
            'sp_disable_very_long_words' => array(),
            'sp_disable_very_long_comments' => array(),
            'sp_disable_very_newline_comments' => array(),

            // Sweeping > Code
            'sp_wp_remove_metas' => array('deleteCache'),
            'sp_into_head_insert_code' => array('deleteCache'),
            'sp_before_body_end_insert_code' => array('deleteCache'),

            // Sweeping > Forbiden types
            'sp_forbidden_attachment' => array('deleteCache','updateHtaccess'),
            'sp_forbidden_day' => array('deleteCache','updateHtaccess'),
            'sp_forbidden_author' => array('deleteCache','updateHtaccess'),
            'sp_forbidden_special_feeds' => array('deleteCache','updateHtaccess'),

        );
    }

    function addInfo(){
        require_once 'spSettingsAdminInfo.php';
        return $this->info = $spSettingsAdminInfo;
    }

    function printHeader( $title ){ echo "<tr><td colspan=2><h3>$title</h3></td></tr>"; }

    function printCheckboxes( $option_name ){
    ?>
      <tr>
          <th scope="row"><?php echo $this->info[ $option_name ]->title; ?>:</th>
          <td>
            <p>
                <label><input type="checkbox" id="chckboxes_<?php echo $option_name; ?>" <?php
                if( $this->settingsData[ $option_name ]->possible == $this->settingsData[ $option_name ]->value ){
                    echo 'checked="checked" ';
                }
                ?>/><strong>&nbsp;All&nbsp;possible:</strong></label>
                <br />
                <?php foreach ($this->settingsData[ $option_name ]->possible as $key=>$option_title) { ?>
                  <label><input type="checkbox" value="<?php echo $option_title; ?>" name="<?php echo $option_name; ?>[]" class="chckboxes_<?php echo $option_name; ?>" <?php
                         if( in_array($option_title, $this->settingsData[ $option_name ]->value) ) echo 'checked="checked" ';
                         ?>/>
                          <?php
                          if( isSet($this->info[ $option_name ]->valuetitles)) {
                              echo $this->info[ $option_name ]->valuetitles[ $option_title ].'</label><br />';
                          } else {
                              echo $option_title.'</label> &nbsp;';
                          }
                          ?>
                <?php } ?>
                <input type="hidden" name="<?php echo $this->settingsData[ $option_name ]->check_by; ?>" value="<?php echo $option_name; ?>" />
            </p>
            <p class="description"><?php echo $this->info[ $option_name ]->description; ?></p>
            <script type="text/javascript">
            // <?php echo $option_name; ?>

            jQuery('#chckboxes_<?php echo $option_name; ?>').change( function(){
                  if( 'checked' == jQuery('#chckboxes_<?php echo $option_name; ?>').attr('checked') ) {
                      jQuery('.chckboxes_<?php echo $option_name; ?>').attr('checked', 'checked');
                  }
              });

            jQuery('.chckboxes_<?php echo $option_name; ?>').change( function(){
                  if( 'checked' != '' + jQuery(this).attr('checked') ) {
                      jQuery('#chckboxes_<?php echo $option_name; ?>').removeAttr('checked');
                  }
              });
            </script>
          </td>
      </tr>
    <?php
    }

    function printOptions( $option_name ){
    ?>
      <tr>
          <th scope="row"><?php echo $this->info[ $option_name ]->title; ?>:</th>
          <td>
            <p>
              <?php foreach ($this->info[ $option_name ]->option as $key=>$option_title) { ?>
                <label>
                    <input type="radio" name="<?php echo $option_name; ?>" value="<?php echo $key; ?>" <?php
                         if( $key == $this->settingsData[ $option_name ]->value ) echo 'checked="checked" ';
                    ?>/> <?php echo $option_title; ?></label><br />

              <?php } ?>
            </p>
            <p class="description"><?php echo $this->info[ $option_name ]->description; ?></p>
          </td>
      </tr>
    <?php
    }

    function printStrings( $option_name ){
    ?>
      <tr>
          <th scope="row"><?php echo $this->info[ $option_name ]->title; ?>:</th>
          <td>
              <p>
              <?php
                foreach ($this->settingsData[ $option_name ]->value as $key=>$value) {
                    if( !empty($value) ){
                        echo '<input type="text" name="'.$option_name.'[]" value="'.$value.'" /> or ';
                    }
                }
                echo '<input type="text" name="'.$option_name.'[]" value="" /> or ';
                echo '<input type="text" name="'.$option_name.'[]" value="" /> or ';
                echo '<input type="text" name="'.$option_name.'[]" value="" />';
              ?>
              </p>
              <p class="description"><?php echo $this->info[ $option_name ]->description; ?></p>
          </td>
      </tr>
    <?php
    }

    function printText( $option_name ){
      ?>
      <tr>
          <th scope="row"><?php echo $this->info[ $option_name ]->title; ?>:</th>
          <td>
            <textarea name="<?php echo $option_name; ?>" id="<?php echo $option_name; ?>" class="large-text code" rows="7"><?php
              echo get_option( $option_name, '' );
            ?></textarea>
            <p class="description"><?php echo $this->info[ $option_name ]->description; ?></p>
          </td>
      </tr>
      <?php
    }

    function printFile( $option_name ){
      ?>
      <tr>
          <th scope="row"><?php echo $this->info[ $option_name ]->title; ?>:</th>
          <td>
            <p>
              <label><input type="radio" id="<?php echo $option_name; ?>_enable_0" name="<?php echo $option_name; ?>_enable" value="0"<?php if( 0 == get_option( $option_name."_enable", 0 ) ) echo ' checked="checked"'; ?>> Leave it to plugin</label>
              <br />
              <label><input type="radio" id="<?php echo $option_name; ?>_enable_1" name="<?php echo $option_name; ?>_enable" value="1"<?php if( 1 == get_option( $option_name."_enable", 0 ) ) echo ' checked="checked"'; ?>> I will write it myself </label>
            </p>
            <textarea name="<?php echo $option_name; ?>" id="<?php echo $option_name; ?>" class="large-text code" rows="10"<?php if( 0 == get_option( $option_name."_enable", 0 ) ) echo ' style="display:none"'; ?>><?php
              if( is_readable( $this->settingsData[ $option_name ]->path ) ) {
                  $cnt = file_get_contents( $this->settingsData[ $option_name ]->path );
                  $cnt = htmlentities($cnt);
                  echo $cnt;
              }else if( !empty( $this->settingsData[ $option_name ]->value ) ) {
                  echo $this->settingsData[ $option_name ]->value;
              }
            ?></textarea>
            <p class="description"><?php echo $this->info[ $option_name ]->description; ?></p>
            <script type="text/javascript">
            // <?php echo $option_name; ?>

            jQuery('#<?php echo $option_name; ?>_enable_0').change( function(){
                  if( 'checked' == jQuery('#<?php echo $option_name; ?>_enable_0').attr('checked') ) {
                      jQuery('#<?php echo $option_name; ?>').css('display', 'none');
                  }
              });

            jQuery('#<?php echo $option_name; ?>_enable_1').change( function(){
                  if( 'checked' == jQuery('#<?php echo $option_name; ?>_enable_1').attr('checked') ) {
                      jQuery('#<?php echo $option_name; ?>').css('display', 'block');
                  }
              });
            </script>
          </td>
      </tr>
      <?php
    }

    function printSave( ){
      ?><tr><th> &nbsp; </th><td><p class="submit">
        <input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo __( 'Save Changes' ); ?>">
        </p></td></tr><?php
    }

}
  
