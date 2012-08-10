<?php

function_exists ('is_admin') or exit;
is_admin()                   or exit;
defined('SP_PLUGIN_ROOT')    or exit;
defined('SP_ABSPATH')        or exit;

require_once 'spHooks.php';

class spHooksAdmin extends spHooks {
    public $hooks;

    function __construct( ){
        $this->loadWPHooks();
        $this->settings = spClasses::get('Settings');
        $this->hooks = array();
    }
  	
  	function add( $hookName ){
        if( is_array( $hookName ) ){
            foreach ($hookName as $key=>$value) {
                if( ! in_array($value, $this->hooks) ){
                    $this->hooks[] = $value;
                }
            }
        }else if( ! in_array($hookName, $this->hooks) ){
            $this->hooks[] = $hookName;
        }
    }
    
    function run(){
        if( defined('WP_ALLOW_MULTISITE') and WP_ALLOW_MULTISITE ){
            echo '<div id="message" class="error"><p>ERROR: Sorry, this plugin does not work on multisites.</p></div>';
            return;
        }

        foreach ($this->hooks as $index=>$hook) {
            switch ($hook) {
                case 'deleteCache':
                    $spCache = spClasses::get('Cache');
                    if( $spCache->deleteAllInCache() ){
                        echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Cache was deleted</strong></p></div>';
                    }else{
                        echo '<div id="message" class="error"><p>ERROR: unalbe to delete all cached files!</pre> NOT FOUND!!!</p></div>';
                    }
                    break;
                case 'updateHtaccess':
                    $spSettings = spClasses::get('Settings');

                    if( 1 == $spSettings->file_edit_htaccess_enable ){
                        break;
                    }
                    
                    $spHtaccess = spClasses::get('Htaccess');
                    if( $spHtaccess->refreshHtaccess() ){
                        echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>File .htaccess was updated.</strong></p></div>';
                    }else{
                        echo '<div id="message" class="error"><p>ERROR: unalbe update .htaccess</p></div>';
                    }
                    break;
                case 'updateSitemaps':
                    $settings = spClasses::get('Settings');

                    if( empty( $settings->sitemaps_enabled ) ){
                        break;
                    }

                    $_sp_Sitemaps = spClasses::get('Sitemaps');
                    $_sp_Sitemaps->refreshAll();
                    if( !empty( $_sp_Sitemaps->error ) ){
                        foreach ($_sp_Sitemaps->error as $index=>$value) {
                            echo '<div class="error below-h2"><p><strong>'.$value.'</strong></p></div>';
                        }
                    }else{
                        if( 0 == get_option( 'sp_file_root_sitemap_xml_enable', 0 ) ) {
                            echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Sitemaps were updated.</strong></p></div>';
                        }
                    }
                    break;
                default:
                    die('Dunno what '.$value.' hook means ...');
                    break;
            }
        }
    }
}