<?php

require_once 'spMenuData.php';

class spMenuAdmin extends spDataMenu{
    public $menuData;
    public $menu;
    public $submenu;
    public $tab;
    public $capability;

    function callClassMethod($class, $method, $data){
        if( defined('WP_ALLOW_MULTISITE') and WP_ALLOW_MULTISITE ){
            echo '<div id="message" class="error"><p>ERROR: Sorry, this plugin does not work on multisites.</p></div>';
            return;
        }

        $c = spClasses::get( $class );

        if( empty( spClasses::$errors ) ){
            if($data){
                $c->$method($data);
            }else{
                $c->$method();
            }
        }
        
        spClasses::printInfos();
    }

    function printAdminPage(){
        global $_GET;

        // MENU in root

        if( empty( $this->menu ) or empty($this->menuData[ $this->menu ]) ) {
            echo '<div id="message" class="error"><p>FATAL ERROR: I do not know what to print and I even do not how you get there - error in class spMenuAdmin->printAdminPage()</p></div>';
            return;
        }

        if( empty($this->menuData[ $this->menu ]['ch']) ){
            if( empty($this->menuData[ $this->menu ]['class']) or empty($this->menuData[ $this->menu ]['method']) ){
                echo '<div id="message" class="error"><p>FATAL ERROR: I do not know what method call for '.$this->menu.' in spMenuAdmin->printAdminPage():';
                echo '<pre>'; print_r($this->menuData[ $this->menu ]);echo '</pre></p></div>';
                return;
            }
            $class = $this->menuData[ $this->menu ]['class'];
            $method = $this->menuData[ $this->menu ]['method'];
            $data = empty( $this->menuData[ $this->menu ]['data'] ) ? null : $this->menuData[ $this->menu ]['data'];
            echo '<div class="wrap">';
            echo '<div id="';
            echo isSet($this->menuData[ $this->menu ]['icon']) ? $this->menuData[ $this->menu ]['icon'] : 'icon-themes';
            echo '" class="icon32"><br></div><h2>'.$this->menuData[ $this->menu ]['title'].'</h2>';
            $this->callClassMethod($class, $method, $data);
            echo '</div>';

            return;
        }

        // subMENU

        if( empty( $this->submenu ) or empty($this->menuData[ $this->menu ]['ch'][ $this->submenu ]) ) {
            echo '<div id="message" class="error"><p>FATAL ERROR: I do not know what to print and I even do not how you get there - error in class spMenuAdmin->printAdminPage()</p></div>';
            return;
        }

        if( empty($this->menuData[ $this->menu ]['ch'][ $this->submenu ]['ch']) ){
            if( empty($this->menuData[ $this->menu ]['ch'][ $this->submenu ]['class']) or empty($this->menuData[ $this->menu ]['ch'][ $this->submenu ]['method']) ){
                echo '<div id="message" class="error"><p>FATAL ERROR: I do not know what method call for '.$this->menu.' / '.$this->submenu.' in spMenuAdmin->printAdminPage():';
                echo '<pre>'; print_r($this->menuData[ $this->menu ]['ch'][ $this->submenu ]);echo '</pre></p></div>';
                return;
            }
            $class = $this->menuData[ $this->menu ]['ch'][ $this->submenu ]['class'];
            $method = $this->menuData[ $this->menu ]['ch'][ $this->submenu ]['method'];
            $data = empty( $this->menuData[ $this->menu ]['ch'][ $this->submenu ]['data'] ) ? null:  $this->menuData[ $this->menu ]['ch'][ $this->submenu ]['data'];
            echo '<div class="wrap">';
            echo '<div id="';
            echo isSet($this->menuData[ $this->menu ]['ch'][ $this->submenu ]['icon']) ? $this->menuData[ $this->menu ]['ch'][ $this->submenu ]['icon'] : 'icon-themes';
            echo '" class="icon32"><br></div><h2>'.$this->menuData[ $this->menu ]['ch'][ $this->submenu ]['title'].'</h2>';
            $this->callClassMethod($class, $method, $data);
            echo '</div>';

            return;
        }
        
        // TABS & subMENU
        $childs = $this->menuData[ $this->menu ]['ch'][ $this->submenu ]['ch'];
        $this->getTab($childs);

        echo '<div class="wrap">';
        echo '<div id="';
        echo isSet($this->menuData[ $this->menu ]['ch'][ $this->submenu ]['icon']) ? $this->menuData[ $this->menu ]['ch'][ $this->submenu ]['icon'] : 'icon-themes';
        echo '" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($childs as $subslug=>$data) {
            echo '<a href="./admin.php?page='.$this->submenu.'&amp;subpage='.$subslug.'" class="nav-tab'.
                 (( $subslug == $this->tab )?' nav-tab-active':'').
                 '">'.str_replace(array('<','>'),array('&lt;','&gt;'),str_replace('&','&amp;',$data['title'])).'</a>';
        }
        echo '</h2>';

        if( empty($childs[ $this->tab ]['class']) or empty($childs[ $this->tab ]['method']) ){
            echo '<div id="message" class="error"><p>FATAL ERROR: I do not know what method call for '.$this->menu.' / '.$this->submenu.' / '.$this->tab.' in spMenuAdmin->printAdminPage():';
            echo '<pre>'; print_r($childs[ $this->tab ]);echo '</pre></p></div>';
            return;
        }
        $class = $childs[ $this->tab ]['class'];
        $method = $childs[ $this->tab ]['method'];
        $data = ( empty( $childs[ $this->tab ]['data'] ) ) ? null : $childs[ $this->tab ]['data'];
        $this->callClassMethod($class, $method, $data);

        echo '</div>';
    }
    
    function create(){
        global $_GET;

        $this->menu = null;
        $this->submenu = null;

        foreach ($this->menuData as $menuslug=>$menudata) {
            if( $menuslug == $_GET['page'] ) $this->menu = $menuslug;
            if( empty( $menudata['ch'] ) ){
                add_menu_page( $menudata['title'], $menudata['title'], 'administrator', $menuslug, array( $this, 'printAdminPage' ) );
            }else{
                add_menu_page( $menudata['title'], $menudata['title'], 'administrator', $menuslug );
                foreach ($menudata['ch'] as $submenuslug=>$submenudata) {
                    add_submenu_page( $menuslug, $submenudata['title'], $submenudata['title'], 'administrator', $submenuslug, array( $this, 'printAdminPage' ) );
                    if( $submenuslug == $_GET['page'] ){
                        $this->menu = $menuslug;
                        $this->submenu = $submenuslug;
                    }
                }
            }
        }
    }

    function getTab($childs){
        global $_GET;

        $subpage = ( empty($_GET['subpage']) ) ? NULL : $_GET['subpage'];
        if( NULL != $subpage ){
            if( empty( $childs[$subpage] ) ){
                $subpage = NULL;
            }
        }

        if( NULL == $subpage ){
            $tmp = array_keys($childs);
            $subpage = $tmp[0];
        }

        return ( $this->tab = $subpage );
    }
}

