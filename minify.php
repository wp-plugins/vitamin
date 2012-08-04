<?php
isSet($_GET['ext']) or die('no GET[ext] param');
isSet($_GET['gzip']) or die('no GET[gzip] param');
isSet($_GET['exp']) or die('no GET[exp] param');
isSet($_GET['path']) or die('no GET[path] param');

$ext  = $_GET['ext'];
$gzip = $_GET['gzip'];
$exp  = $_GET['exp'];
$path = $_GET['path'];

define('SP_ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) );

$path = 'wp-content'.DIRECTORY_SEPARATOR.$path;
$file = SP_ABSPATH.DIRECTORY_SEPARATOR.$path;

require_once 'classes/spClasses.php';

switch ($ext) {
    case 'css':
          $minifier = spClasses::get('MinifierCSS');
          break;
    case 'js':
          $minifier = spClasses::get('Minifier');
          break;
    default:
          $minifier = spClasses::get('Minifier');
          break;
}

$minifier->setExt( $ext );
$minifier->setGzip( 'true' == $gzip );
$minifier->setExp( 1 * $exp );

$minifier->load( SP_ABSPATH.DIRECTORY_SEPARATOR.$path );
$minifier->minify();

$save_to = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
if( 'www.' == substr($save_to,0,4) ) $save_to = substr($save_to,4);

$minifier->save( SP_ABSPATH.DIRECTORY_SEPARATOR.'wp-content'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$save_to );
$minifier->showOutput( );
