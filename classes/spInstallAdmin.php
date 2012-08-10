<?php

require_once 'spBoard.php';

class spInstallAdmin extends spBoard {

    function printAdminPage(){
        global $_GET;

        $this->printOkAndbadCss();

        $this->beforeForm();

        $this->beforeBox('Directories, Files and Permalinks');
        echo '<table class="widefat">';
        $this->showInstall();
        echo '</table>';
        $this->afterBox();

        $this->columnSeparator();

        if( !empty($_GET['uninstall']) and $_GET['uninstall'] == 'true' ){
            $this->showUninstallBlocks();
        }else if( !empty($_GET['install']) and $_GET['install'] == 'true' ){
            $this->showRightInstallItBlocks();
        }else if( !empty($_GET['reset_settings']) and $_GET['reset_settings'] == 'true' ){
            $this->showRightResetSettingsBlocks();
        }
        $this->showInstallOrUninstall();

        $this->afterForm();
    }

    function showInstallOrUninstall(){
        $this->beforeBox('Reset Plugin Settings');
        echo '<p>Returns all plugin settings to default. Leaves sitemaps, 404, blocks and statistics.</p>';
        $this->showButton('reset_settings=true', 'Reset Settings');
        $this->afterBox();

        $this->beforeBox('Force Reinstall');
        echo '<p>If there are some serious problems, just click on this button. It helps in 90% of all problems.</p>';
        $this->showButton('install=true', 'Force Reinstall');
        $this->afterBox();

        $this->beforeBox('Uninstall');
        echo '<p>Well, nothing is perfect.</p>';
        echo '<p>If you have problems with this plugin or you are not just satisfied with that, just click on Uninstall button. Everything will be changed to before installation state. (If it will be possible).</p>';
        $this->showButton('uninstall=true', 'Uninstall');
        $this->afterBox();
    }

    function showRightResetSettingsBlocks(){
        $settingsAdmin = spClasses::get('Settings');
        if( empty($_POST['keep_settings']) ){
            $settingsAdmin->setDefaultSettings();
            echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings set to default.</strong></p></div>';
        }
        $settingsAdmin->saveSettings();
    }

    function showRightInstallItBlocks(){
        global $_POST;
        $this->beforeBox('New WP root index.php file');
        $this->checkWPRootIndexPHP();
        $this->afterBox();

        $this->beforeBox('File with URL to block');
        $this->checkBlockedPages();
        $this->afterBox();

        $this->beforeBox('File with Sitemap List');
        $this->checkSitemapList();
        $this->afterBox();

        require_once 'spHooksAdmin.php';
        $adminHooks = new spHooksAdmin();
        $adminHooks->add('deleteCache');
        $adminHooks->add('updateHtaccess');
        $adminHooks->add('updateSitemaps');
        $adminHooks->run();

        $this->beforeBox('File setting refresh and update');
        echo '<p class=ok>Checked!</p>';
        $this->afterBox();
    }
    
    function showUninstallBlocks(){
        $all_OK = TRUE;
        
        $this->beforeBox('Returning root index.php file to orginal state.');

        $orginal_index = @file_get_contents(SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR.'index_php_old.txt');
        if ( FALSE === $orginal_index ){
            $all_OK = FALSE;
        }

        if( !$orginal_index ){
            echo '<p class=bad>Fatal error: Cannot open orginal index.php file backup: '.SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR.'index_php_old.txt</p>';
        }else{
            $status = ( FALSE !== @file_put_contents(SP_ABSPATH.'index.php', $orginal_index) );
            if( !$status ){
                $all_OK = FALSE;
                echo '<p class=bad>Fatal error: Cannot open root index.php file to write orginal backup: '.SP_ABSPATH.'index.php</p>';
            }else{
                echo '<p class=ok>Root file index.php was replaced by orginal backup.</p>';
            }
        }

        $this->afterBox();

        $this->beforeBox('Returning .htaccess file to orginal state.');
        $ht = spClasses::get('Htaccess');
        if( $ht->refreshHtaccess( FALSE ) ){
            echo '<p class=ok>Plugin code was removed from file .htaccess.</p>';
        }else{
            echo '<p class=bad>Fatal error: Cannot open .htaccess file or remove plugin code.</p>';
            $all_OK = FALSE;
        }
        $this->afterBox();

        if( $all_OK ){
            $this->beforeBox('Returning .htaccess file to orginal state.');
            echo '<p>Now you may just go to <a href="./plugins.php">Plugins</a> and deactivate this plugin completly.</p>';
            echo '<p style="text-align:right"><a href="./plugins.php" class="button-primary">Visit "Plugins" in admin menu to deactivate</a></p>';
            $this->afterBox();
        }
    }

    function showInstallDir($name, $path){
        ?>
        <tr>
            <td><strong><?php echo $name; ?>: </strong> </td>
            <td class="status"><?php
              @chmod($path, 0755);
              if( ! is_dir($path) ){
                  if( @mkdir($path) ){
                      if( is_writeable($path) ){
                          echo '<span class=ok>Created</span>';
                      }else{
                          echo 'Fatal & error: Can not write into '.$path.'!';
                          exit;
                      }
                  }else{
                      echo 'Fatal error: Directory '.$path.' cannot be created';
                      exit;
                  }
              }else{
                  if( is_writable($path) ) {
                      echo '<span class=ok>OK</span>';
                  }else{
                      if( is_writeable($path) ){
                          echo '<span class=ok>Writing enabled</span>';
                      }else{
                          echo 'Fatal error: Cannot write into '.$path;
                          exit;
                      }
                  }
              }
            ?></td>
        </tr>
        <?php
    }

    function showInstallFile($name, $path){
        ?>
        <tr>
            <td><strong><?php echo $name; ?>: </strong> </td>
            <td class="status"><?php
              @chmod($path, 0755);
              if( ! is_file($path) ){
                  if( $fp=fopen($path,'wt') ){
                      fclose($fp);
                      if( is_writeable($path) ){
                          echo '<span class=ok>Created</span>';
                      }else{
                          echo 'Fatal & error: Can not write into '.$path.'!';
                          exit;
                      }
                  }else{
                      echo 'Fatal error: File '.$path.' cannot be created';
                      exit;
                  }
              }else{
                  if( is_writable($path) ) {
                      echo '<span class=ok>OK</span>';
                  }else{
                      if( is_writeable($path) ){
                          echo '<span class=ok>Writing enabled</span>';
                      }else{
                          echo 'Fatal error: Cannot write into '.$path;
                          exit;
                      }
                  }
              }
            ?></td>
        </tr>
        <?php
    }
    
    function showSeparator(){
        ?>
        <tr>
            <td> &nbsp; </td>
            <td> &nbsp; </td>
        </tr>
        <?php
    }

    function showInstall(){
        $others = SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR;
        $sitemaps = SP_PLUGIN_ROOT.'sitemaps'.DIRECTORY_SEPARATOR;

        $this->showInstallDir( 'Cache Directory', SP_ABSPATH.'wp-content'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR );

        $this->showSeparator();

        $this->showInstallDir( 'Settings Directory', $others );
        $this->showInstallFile( '404 Log File', $others.'404.txt');
        $this->showInstallFile( '403 Log File', $others.'403.txt');
        $this->showInstallFile( 'File with RFI blocks', $others.'blocks.txt');
        $this->showInstallFile( 'New root WP index.php file', $others.'index_php_new.txt');
        $this->showInstallFile( 'Old root WP index.php file (backup)', $others.'index_php_old.txt');
        $this->showInstallFile( 'Redirections File', $others.'redir.txt');
        $this->showInstallFile( 'Settings File', $others.'settings.txt');
        $this->showInstallFile( 'Sitemap Settings File', $others.'sitemaps.txt');
        $this->showInstallFile( 'PHP Stats File', $others.'stats.txt');

        $this->showSeparator();

        $this->showInstallDir( 'Sitemaps Directory', SP_PLUGIN_ROOT.'sitemaps'.DIRECTORY_SEPARATOR );
        $this->showInstallFile( 'Sitemap Root File', $sitemaps.'index-sitemap.xml');
        $this->showInstallFile( 'Sitemap Root File (compressed)', $sitemaps.'index-sitemap.xml.gz');
        $this->showInstallFile( 'File robots.txt', $sitemaps.'robots.txt');
        $this->showInstallFile( 'File robots.txt (compressed)', $sitemaps.'robots.txt.gz');

        $this->showSeparator();

        $this->showInstallFile( 'Writing into .htaccess', ABSPATH.'.htaccess');
        $this->showInstallFile( 'Writing into WP Root index.php', ABSPATH.'index.php');

        $this->showSeparator();
        ?>
        <tr><td><strong>Permalinks: </strong> </td><td class="status"><?php
              if( '' == get_option('permalink_structure') ){
                  update_option('permalink_structure','/%postname%/');
                  echo '<span class=ok>Updated (sorry)</span>';
              }else{
                  echo '<span class=ok>OK</span>';
              }
        ?></td></tr>
        <tr><td><strong>Permalink Structure: </strong> </td>
            <td><?php echo get_option('permalink_structure'); ?></td>
        </tr>
        <?php
    }
    
    function checkWPRootIndexPHP(){
        $index = file_get_contents(SP_ABSPATH.'index.php');

        $find = str_replace(SP_ABSPATH,'',SP_PLUGIN_ROOT);
        
        $find = str_replace(DIRECTORY_SEPARATOR,"' . DIRECTORY_SEPARATOR . '", $find);
        
        if( FALSE === strpos($index, $find) ){
            $new_index = file_get_contents(SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR.'index_php_new.txt');
            $new_index = str_replace('%%%THERE-MUST-BE-PLUGIN-DIRECTORY%%%', $find, $new_index);

            if( ! is_writable(SP_ABSPATH.'index.php') ){
                echo '<p>Cannot write into <code>'.SP_ABSPATH.'index.php</code></p>';
                return;
            }
            echo '<p class=ok>Root index.php is updated and ready.</p>';
            file_put_contents(SP_ABSPATH.'index.php', $new_index);
            file_put_contents(SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR.'index_php_old.txt', $index);
        }else{
            echo '<p class=ok>Root index.php is ready.</p>';
        }
    }
    
    function checkBlockedPages(){
        if( 0 == filesize( SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR.'blocks.txt' ) ){
            $bad_boyz_banned = array(  //thumb.php?src=http://blogger.community.wanax.ro/blog.php
                                      '?%anything%src%anything%.%anything%.%anything%.%anything%/%anything%.php%anything%',
                                      '?src=',
                                      '%anything%setup.php%anything%',
                                      '%anything%/db/%anything%',
                                      '%anything%myadmin%anything%',
                                      '%anything%phpmanager%anything%',
                                      '%anything%phpmyadmin%anything%',
                                      '%anything%phpmy-admin%anything%',
                                      '%anything%php-my-admin%anything%',
                                      '%anything%php-admin%anything%',
                                      '%anything%/pma/%anything%',
                                      '%anything%/sqlmanager/%anything%',
                                      '%anything%/sqladmin/%anything%',
                                      '%anything%webadmin%anything%',
                                      '%anything%webdb%anything%',
                                      '%anything%websql%anything%',
                                      '%anything%/xamp/%anything%',
                                    );
            echo '<p class=ok>Adding:</p><p><code>';
            foreach ( $bad_boyz_banned as $key=>$value) {
                echo $value."<br />";
            }
            echo '</code></p>';
            file_put_contents(SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR.'blocks.txt', implode("\n",$bad_boyz_banned) );
        }else{
            echo '<p class=ok>There is a few possible but blocked bad url addresses.</p>';
        }
    }
    
    function checkSitemapList(){
        if( 0 == filesize( SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR.'sitemaps.txt' ) ){
            $sitemaps = array();
            file_put_contents(SP_PLUGIN_ROOT.'others'.DIRECTORY_SEPARATOR.'sitemaps.txt',
                "normal -> taxonomies -> tax_all -> 0\n".
                "normal -> post-and-pages -> post_all -> 0\n".
                "image -> images -> post_all -> 0"
            );
            echo '<p class=ok>Adding default sitemaps.</p>';
        }else{
            echo '<p class=ok>There are a few sitemaps.</p>';
        }

    }
}
