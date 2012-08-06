<?php
isSet($_GET['ext']) or die('no GET[ext] param');
isSet($_GET['gzip']) or die('no GET[gzip] param');
isSet($_GET['exp']) or die('no GET[exp] param');
isSet($_GET['path']) or die('no GET[path] param');

$ext  = $_GET['ext'];
$gzip = $_GET['gzip'];
$exp  = $_GET['exp'];
$path = $_GET['path'];

if( FALSE !== strpos($path, "../") ){ die("There are forbidden substrings in file path"); }
if( $ext != substr($path, - strlen($ext)) ) { die("Extension does not fit with extension in filename"); }
if( 'php' == strtolower($ext) ) { die("Adding headers to php files is forbidden"); }
if( 'phtml' == strtolower($ext) ) { die("Adding headers to phtml files is forbidden"); }

define('SP_ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) );

$path = 'wp-content'.DIRECTORY_SEPARATOR.$path;
$file = SP_ABSPATH.DIRECTORY_SEPARATOR.$path;

require_once 'classes/spClasses.php';

$minifier = spClasses::get('Minifier');
          
$minifier->setExt( $ext );
$minifier->setGzip( 'true' == $gzip );
$minifier->setExp( 1 * $exp );

if( 'htm' == $ext ){
    // but... if this is case of html => mod_headers.c in htaccess is DISABLED
    // so, we have to save stat case htaccess_hit
    $stats = spClasses::get('Stats');
    $stats->save('htaccess_hit');
}

$minifier->showOutput( $file );

