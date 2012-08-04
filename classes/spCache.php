<?php
class spCache {

    public $settings;

    public $stats;
    
    public $cacheDir; // cacheDir cache directory path

    public $uri; // uri Cache settings
    
    public $isFeed;

    public $cachedFilePath; // cachedFilePath Path to cached file (even if not exists)

    public $error; // error indicates, that error occured -> there will be not any cache :(

  	function __construct(){
        $this->cacheDir = SP_ABSPATH.'wp-content'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;

        $this->settings = spClasses::get('Settings');
        $this->stats = spClasses::get('Stats');
        $this->uri = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        $this->isFeed = FALSE;
    }

    function shouldBeSaved(){
        global $_GET;
        if( !empty($_GET) )
            return FALSE;

        global $_POST;
        if( !empty($_POST) )
            return FALSE;

        foreach ($this->settings->cache_disabled_substrings as $key=>$value)
            if( !empty($value) and FALSE !== strpos($this->uri, $value)  )
                return FALSE;

        if( empty($this->settings->cache_level) )
            return FALSE;

        global $_COOKIE;
        if( !empty($_COOKIE) ){
            foreach ($_COOKIE as $key=>$value){
                if( FALSE !== strpos($key, "wordpress_logged_in_" ) ) return FALSE;
                if( FALSE !== strpos($key, "comment_author_" ) ) return FALSE;
            }

            if( ! empty($_COOKIE['PHPSESSID']) ) {
                if( 'disable_cache' == $this->settings->cache_reaction_on_session ) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }
    
    function getSessionPlusString(){

        if( 'cache_by_session' != $this->settings->cache_reaction_on_session ){
            return '';
        }

        $session_setting = '';

        global $_COOKIE;
        if( empty($_COOKIE) ) return '';
        if( empty($_COOKIE['PHPSESSID']) ) return '';

        if( ! @session_start() ) return '';
        
        global $_SESSION;
        if( !empty($_SESSION) ){
            ksort($_SESSION);
            foreach ($_SESSION as $key=>$value) {
                $session_setting .= "|||$key|=|$value";
            }
            $session_setting = "_".md5($session_setting);
        }
        return "session_".$session_setting;
    }
    
    public function findNameOfCachedFile( $uri = FALSE ){
        if( $uri ){
            $cachedFilePath = $uri;
        }else{
            $cachedFilePath = $this->uri;
        }
        
        if( 'https://' == substr($cachedFilePath,0,8) ) $cachedFilePath = substr($cachedFilePath,8);
        if( 'http://'  == substr($cachedFilePath,0,7) ) $cachedFilePath = substr($cachedFilePath,7);
        if( 'www.'     == substr($cachedFilePath,0,4) ) $cachedFilePath = substr($cachedFilePath,4);

        if( FALSE !== strpos($cachedFilePath,'?')  ){
            $cachedFilePath = substr($cachedFilePath, 0, strpos($cachedFilePath,'?') );
        }

        if ( '/' == substr($cachedFilePath,-1) ) {
            $feeds_uri_end_substring = array(
                '/feed/',
                '/feed/rdf/',
                '/feed/rss/',
                '/feed/rss2/',
                '/feed/atom/',
            );
            $this->isFeed = false;
            foreach ($feeds_uri_end_substring as $key=>$suburl) {
                if( $suburl == substr($cachedFilePath, -strlen($suburl) ) ){
                    $this->isFeed = true;
                }
            }

            if( '/' == substr( $cachedFilePath, -1 ) ){
                $cachedFilePath = substr( $cachedFilePath, 0, -1 );

                if( $this->isFeed ){
                    $cachedFilePath = $cachedFilePath . '_c.xml';
                }else{
                    $cachedFilePath = $cachedFilePath . $this->getSessionPlusString() . '_c.htm';
                }
            }else{
                // TXT, XML ...
            }
        }

        return $this->cachedFilePath = SP_ABSPATH.'wp-content'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$cachedFilePath;
    }

    public function test_IfModifiedSince_vs_CacheFileTime(){
        $visitor_headers = getallheaders();

        if( !isSet($visitor_headers['If-Modified-Since']) ) return FALSE;

        $file_time = filemtime( $this->cachedFilePath );

        if( (strtotime($visitor_headers['If-Modified-Since']) < $file_time ) ) return FALSE;
        
        return TRUE;
    }
    
    public function answer_304_NotModified(){
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime( $this->cachedFilePath )).' GMT', true, 304);
        exit;
    }
    
    public function deleteCache(){
        if( $dir = @dir( $this->cacheDir ) ) {
            while (($file = $dir->read()) !== false) {
                $file = $this->cacheDir.$file;
                if( is_file($file) ) unlink($file);
            }
        }
    }

    public function deleteAllInCache($dir = null){

        $all_ok = TRUE;

        if( null == $dir ){
            $dir = $this->cacheDir;
        }

        $d = dir( $dir );
        while (false !== ($entry = $d->read())) {
            if( '.' == $entry ) continue;
            if( '..' == $entry ) continue;
            if( is_dir( $dir . $entry ) ){
                $all_ok = $all_ok AND $this->deleteAllInCache( $dir . $entry . '/' );
                $all_ok = $all_ok AND @rmdir( $dir . $entry . '/' );
            }else{
                $all_ok = $all_ok AND @unlink( $dir . $entry );
            }
        }

        $d->close();
        return $all_ok;
    }

    public function deleteCacheFile(){
        if( file_exists($this->cachedFilePath) ){
            @unlink($this->cachedFilePath);
        }
    }
    
    public function deleteCacheFileByURL($url){
        $this->findNameOfCachedFile( $url );
        @unlink( $this->cachedFilePath );
    }

    function createParentsDirs( $dir = false ){
        if( $dir ){
            $dir = $dir . DIRECTORY_SEPARATOR;
            if( is_dir( dirname( $dir ) ) ){
                if( ! is_dir($dir) ){
                    @mkdir($dir);
                }
                return;
            }else{
                $this->createParentsDirs( dirname($dir) );
                if( ! is_dir($dir) ){
                    @mkdir($dir);
                }
            }
        }else{
            $this->createParentsDirs( dirname( $this->cachedFilePath ) );
        }
    }

    function showCachedVersionIfPossible(){

        $this->findNameOfCachedFile();
        if ( ! file_exists($this->cachedFilePath) ){
            return FALSE;
        }
        
        if( ! $this->shouldBeSaved() ){
            return FALSE;
        }

        $spStats = spClasses::get('Stats');

        if( $this->test_IfModifiedSince_vs_CacheFileTime() ){
            $spStats->save304();
            $this->answer_304_NotModified();
        }

        $h = getallheaders();

        if( $this->isFeed ){
            header( "Content-Type: text/xml; charset=utf-8", true );
        }else{
            header( "Content-Type: text/html; charset=utf-8", true );
        }

        if( !isSet($h['Accept-Encoding']) or ( FALSE === strpos( strtolower($h['Accept-Encoding']), 'gzip' ) ) or ! file_exists($this->cachedFilePath.'.gz')  ){
            header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($this->cachedFilePath)).' GMT', true);
            readfile($this->cachedFilePath);
        }else{
            header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($this->cachedFilePath.'.gz')).' GMT', true);
            header('Content-Encoding: gzip');
            header('Content-Length: '. filesize($this->cachedFilePath.'.gz') );
            readfile($this->cachedFilePath.'.gz');
        }

        $spStats->saveHit();

        exit;
    }

    public function preCacheByBuffer(){
        ob_start();
    }

    function doStuffWtithBuffer( ){

        $spStats = spClasses::get('Stats');

        $dataToCache = ob_get_contents();
        ob_end_clean();
        $this->createParentsDirs();
        
        $spAntispam = spClasses::get('Antispam');
        $dataToCache = $spAntispam->cleanFromCommentFormValues($dataToCache);

        if( $this->isFeed ) {
            $minifier = spClasses::get('Minifier') ;
        }else{
            $minifier = in_array('htm',$this->settings->mod_minify) ?
                        spClasses::get('MinifierHTML') :
                        spClasses::get('Minifier') ;
        }

        $minifier->setGzip( in_array('htm',$this->settings->mod_gzip_ext) );

        if( $this->isFeed ) {
            $minifier->setExt( 'xml' );
        }else{
            $minifier->setExt( 'htm' );
        }
        
        $minifier->setInput( $dataToCache );

        $minifier->minify();
        
        if( $this->shouldBeSaved() ){
            $minifier->save( $this->cachedFilePath );
        }else{
            $minifier->showOutput( );
            $spStats->saveDisabled();
            exit;
        }
        $minifier->showOutput( );

        if( is_404() ){
            $spStats->saveStat404();
            exit;
        }

        $spStats->saveMiss();
    }
}
  
  
?>