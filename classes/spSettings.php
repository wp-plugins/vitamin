<?php

class spSettings {

    public $settingsData;

  	function __construct(){
        $this->settingsPath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'settings.txt';
        $this->loadSettings();
        $this->getNiceSettings();
    }

    public function getNiceSettings(){
        foreach ($this->settingsData as $key=>$value) {
            if( 'checking' == $value->value ) continue;
            $nice_key = substr($key, 3); // 3 = strlen('sp_')
            $this->$nice_key = $value->value;
        }
    }

    public function loadSettings(){
        $this->setDefaultSettings();

        $sf = @file($this->settingsPath);

        if($sf){
            foreach ($sf as $key=>$value) {
                if( FALSE !== ( $pos = strpos($value, ':') ) ){
                    $k = trim(substr($value,0,$pos));
                    $v = trim(substr($value,$pos+1));
                    if( isSet( $this->settingsData[$k] ) ){
                        switch ($this->settingsData[$k]->type) {
                          case 'option':
                              if( in_array($v, $this->settingsData[$k]->possible ) ){
                                  $this->settingsData[$k]->value = $v;
                              }
                              break;
                          case 'strings':
                              if( FALSE !== strpos($v, ',' ) ){
                                  $v = explode(',', $v);
                                  foreach ($v as $v_key=>$v_value) {
                                      $v_value = trim($v_value);
                                      if( empty($v_value) or in_array( $v_value, $this->settingsData[$k]->mandatory) ){
                                          unset( $v[$v_key] );
                                      }else{
                                          $v[$v_key] = $v_value;
                                      }
                                  }
                              }else if( empty($v) or in_array( $v, $this->settingsData[$k]->mandatory) ){
                                  $v = array();
                              }else{
                                  $v = array( $v );
                              }
                              
                              $v = array_merge($this->settingsData[$k]->mandatory, $v);
                              $this->settingsData[$k]->value = $v;
                              break;
                          case 'checkbox':
                              if( FALSE !== strpos($value, ',' ) ){
                                  $v = explode(',', $v);
                                  foreach ($v as $v_key=>$v_value) {
                                      $v_value = trim($v_value);
                                  }
                              }else if(empty($v)) {
                                  $v = array();
                              }else{
                                  $v = array( $v );
                              }
                              $v = array_intersect($this->settingsData[$k]->possible, $v);
                              $this->settingsData[$k]->value = $v;
                              break;
                          default:
                        	    break;
                        }

                    }
                }
            }
        }
    }

    function setDefaultSettings(){

        global $_SERVER;

        $this->settingsData = array(

        // SEO > SEO Files

        'sp_file_edit_htaccess_enable'=>(object) array('type'=>'option','value'=>0,'possible'=>array(0,1),),
        'sp_file_edit_htaccess'=>(object) array('type'=>'file','value'=>'',
            'path'=>dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR.'.htaccess' ),

        'sp_sitemaps_enabled'=>(object) array('type'=>'option','value'=>1,'possible'=>array(0,1),),

        'sp_file_robots_txt_enable'=>(object) array('type'=>'option','value'=>0,'possible'=>array(0,1),),
        'sp_file_robots_txt'=>(object) array( 'type'=>'file','value'=>'',
            'path'=>dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'sitemaps'.DIRECTORY_SEPARATOR.'robots.txt' ),

        'sp_file_root_sitemap_xml_enable'=>(object) array('type'=>'option','value'=>0,'possible'=>array(0,1),),
        'sp_file_root_sitemap_xml'=>(object) array( 'type'=>'file','value'=>'',
            'path'=>dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'sitemaps'.DIRECTORY_SEPARATOR.'index-sitemap.xml' ),

        'sp_add_rss_to_robots_sitemap'=>(object) array('type'=>'option','value'=>1,'possible'=>array(0,1),),
        'sp_add_atom_to_robots_sitemap'=>(object) array('type'=>'option','value'=>1,'possible'=>array(0,1),),

        // Speed > HTML Cache

        'sp_cache_level'=>(object) array('type'=>'option','value'=>1,'possible'=>array(0,1,2),),
        'sp_cache_reaction_on_comments'=>(object) array('type'=>'option','value'=>1,'possible'=>array(0,1),),
        'sp_cache_reaction_on_session'=>(object) array('type'=>'option','value'=>'disable_cache','possible'=>array('disable_cache','do_not_care','cache_by_session'),),
        'sp_cache_disabled_substrings'=>(object) array('type'=>'strings','value'=>array( ),'mandatory'=>array( ),),

        // Speed > Compression

        'sp_mod_gzip_ext_check'=>(object) array( 'type'=>'checking','value'=>'sp_mod_gzip_ext',),
        'sp_mod_gzip_ext'=>(object) array(
            'type'=>'checkbox',
            'possible'=>array('css','htm','js','txt','xml'),
            'value'=>   array('css','htm','js','txt','xml'),
            'check_by'=>'sp_mod_gzip_ext_check',
        ),

        'sp_mod_minify_check'=>(object) array( 'type'=>'checking','value'=>'sp_mod_minify',),
        'sp_mod_minify'=> (object) array(
            'type'=>'checkbox',
            'possible'=>array('css','htm',),
            'value'=>   array('css','htm',),
            'check_by'=>'sp_mod_minify_check',
        ),

        // Speed > Browser Cache

        'sp_mod_expires_ext_check'=>(object) array( 'type'=>'checking','value'=>'sp_mod_expires_ext',),
        'sp_mod_expires_ext'=>(object) array(
            'type'=>'checkbox',
            'possible'=>array('jpg','jpeg','png','gif','ico','js','css',),
            'value'=>   array('jpg','jpeg','png','gif','ico','js','css',),
            'check_by'=>'sp_mod_expires_ext_check',
        ),

        'sp_mod_expires_time'=>(object) array('type'=>'option','value'=>604800,'possible'=>array(0,604800,1209600,1209600,2419200,31536000,),),

        // Speed > Fast 404

        'sp_fast404_check'=>(object) array( 'type'=>'checking','value'=>'sp_fast404',),
        'sp_fast404'=>(object) array(
            'type'=>'checkbox',
            'possible'=>array('jpg','jpeg','png','gif','ico','js','css','txt','htm','html','php',),
            'value'=>   array('jpg','jpeg','png','gif','ico','js','css','txt','htm','html','php',),
            'check_by'=>'sp_fast404_check',
        ),

        // Security > Main settings

        'sp_WP_admin_allowed_IPs_on'=>(object) array('type'=>'option','value'=>0,'possible'=>array(0,1),),
        'sp_WP_admin_allowed_IPs_list'=>(object) array(
            'type'=>'strings',
            'value'=>( ( function_exists ('is_admin') and is_admin() ) ? array( $_SERVER['REMOTE_ADDR'] ): array() ),
            'mandatory'=>( ( function_exists ('is_admin') and is_admin() )? array( $_SERVER['REMOTE_ADDR'] ): array() ),
        ),

        // Security > Minimal Antispam

        'sp_enable_miniantispam'=>(object) array('type'=>'option','value'=>'off','possible'=>array('off','change_name','disable_website_field',),),
        'sp_enable_miniantispam_referer'=>(object) array('type'=>'option','value'=>1,'possible'=>array(0,1,),),

        // Security > Censorship

        'sp_disable_bad_words'=>(object) array('type'=>'option','value'=>1,'possible'=>array(0,1,),),
        'sp_disable_very_long_words'=>(object) array('type'=>'option','value'=>100,'possible'=>array('off',50,100,),),
        'sp_disable_very_long_comments'=>(object) array('type'=>'option','value'=>4000,'possible'=>array('off',2000,4000,),),
        'sp_disable_very_newline_comments'=>(object) array('type'=>'option','value'=>20,'possible'=>array('off',5,10,20,),),

        // Sweeping > Code

        'sp_wp_remove_metas_check'=>(object) array( 'type'=>'checking','value'=>'sp_wp_remove_metas',),
        'sp_wp_remove_metas'=>(object) array(
            'type'=>'checkbox',
            'possible'=>array('l10n','rsd_link','wlwmanifest_link','adjacent_posts_rel_link_wp_head','wp_generator','feed_links','feed_links_extra','index_rel_link'),
            'value'=>   array('l10n','rsd_link','wlwmanifest_link','adjacent_posts_rel_link_wp_head','wp_generator','feed_links','feed_links_extra','index_rel_link'),
            'check_by'=>'sp_wp_remove_metas_check',
        ),

        'sp_into_head_insert_code'=>(object) array( 'type'=>'text','value'=>'',),
        'sp_before_body_end_insert_code'=>(object) array( 'type'=>'text','value'=>'',),

        // Sweeping > Forbiden types

        'sp_forbidden_attachment'=>(object) array('type'=>'option','value'=>'','possible'=>array('','page','image','imageRedir','404',),),
        'sp_forbidden_day'=>(object) array('type'=>'option','value'=>'','possible'=>array('','month','404','fast404',),),
        'sp_forbidden_author'=>(object) array('type'=>'option','value'=>'','possible'=>array('','home','404','fast404'),),
        'sp_forbidden_special_feeds'=>(object) array('type'=>'option','value'=>'','possible'=>array('','404','fast404'),),
        );
    }
}
  
  
?>