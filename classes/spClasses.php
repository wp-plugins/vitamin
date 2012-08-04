<?php

if (!function_exists('getallheaders')) {
    function getallheaders() {
        foreach($_SERVER as $key=>$value) {
            if (substr($key,0,5)=="HTTP_") {
                $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                $out[$key]=$value;
            }
        }
        return $out;
    }
}

class spClasses{

    public static $errors = null;
    public static $classList = null;
    public static $_classAddingInfo = null;

    public static function addError( $error ){
        if( empty( spClasses::$errors ) ){
            spClasses::$errors = array();
        }
        spClasses::$errors[] = $error;
    }
    
    public static function printInfos(){
        spClasses::printErrors();
        //spClasses::printClassesInfo();
    }

    public static function printErrors(){
        if( !empty( spClasses::$errors ) ){
            foreach ( spClasses::$errors as $key=>$text ) {
                echo '<div id="message" class="error"><p>FATAL ERROR: '.$text.'</p></div>';
            }
        }
    }
    
    public static function addClassesInfo($info){
        if( empty( spClasses::$_classAddingInfo ) ){
            spClasses::$_classAddingInfo = array();
        }
        spClasses::$_classAddingInfo[] = $info;
    }

    public static function printClassesInfo(){
        if( !empty( spClasses::$_classAddingInfo ) ){
            foreach ( spClasses::$_classAddingInfo as $key=>$text ) {
                echo "<p>$key : $text</p>";
            }
        }
    }

    public static function set($className, $instance){
        spClasses::$classList[$className] = $instance;
    }

    public static function get($className){
        spClasses::addClassesInfo("Want $className");

        $className = "sp".$className;

        if( empty( spClasses::$classList ) ){
            spClasses::$classList = array();
        }

        if( !empty( spClasses::$classList[$className] ) ){
            return spClasses::$classList[$className];
        }

        if( !empty( spClasses::$classList[$className.'Admin'] ) ){
            return spClasses::$classList[$className.'Admin'];
        }

        if( function_exists('is_admin') and is_admin() and file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . $className . 'Admin.php' ) ){
            $className = $className.'Admin';
        }

        if( ! file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . $className . '.php' ) ){
            spClasses::$errors[] = 'FATAL ERROR: class '.$className.' does not exist!';
            return;
        }

        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . $className . '.php';

        spClasses::$classList[$className] = new $className();

        spClasses::addClassesInfo("<b>Created $className</b>");

        return spClasses::$classList[$className];
    }

}

