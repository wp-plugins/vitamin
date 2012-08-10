<?php
/*
Plugin Name: Vitamin
Plugin URI: http://vitamin.seopeter.com/
Description: This all is about SEO, Speed and Security.
Version: 1.2.0
Author: SEO Peter
Author URI: http://seopeter.com/
*/

if( ! defined('SP_PLUGIN_ROOT') ) define('SP_PLUGIN_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR );
if( ! defined('SP_PLUGIN_URL') )  define('SP_PLUGIN_URL', plugin_dir_url(__FILE__) );

define('SP_ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR);

require_once 'classes/spClasses.php';
spClasses::get('Hooks');
spClasses::get('Settings');

if( function_exists ('is_admin') and is_admin() ){
    require_once 'classes/spMenuAdmin.php';

    add_action('admin_menu', array( new spMenuAdmin(), 'create'));

  	function plugin_setting_links($links, $file ) {
  			$this_plugin = plugin_basename(__FILE__);
      	if ( $file == $this_plugin ) {
      			$settings_link = '<a href="./admin.php?page=sp_dashboard&subpage=install&uninstall=true">Uninstall</a>';
      			array_unshift($links, $settings_link); // before other links
      			$settings_link = '<a href="./admin.php?page=sp_dashboard&subpage=install&install=true">Install</a>';
      			array_unshift($links, $settings_link); // before other links
        }
        return $links;
  	}

		add_filter('plugin_action_links', 'plugin_setting_links', 10, 2 );
}

?>
