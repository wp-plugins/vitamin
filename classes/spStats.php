<?php
class spStats {
    public $statsPath; // path to Stats file
    public $statsData;

    /**
      * Stats class constructor
      */
    function __construct(){

        global $_GET;
                           // that stuff down means ../
        $this->statsPath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'stats.txt';

        if( !empty( $_GET ) and !empty( $_GET['clear_cache_stats'] ) and ( function_exists('is_admin') ) and is_admin() ){
            $this->clearStats();
            return;
        }

        $this->loadStats();

    }

    function setDefaultStats(){
        return $this->statsData = array(
            'htaccess_miss' => 0,
            'htaccess_hit' => 0,
            'miss' => 0,
            'hit' => 0,
            'disabled' => 0,
            'admin' => 0,
            '301' => 0, // 301 Moved Permanently
            '302' => 0, // 302 Found (originally temporary redirect)
            '304' => 0, // 304 Not Modified
            '307' => 0, // 307 Temporary Redirect
            '403' => 0, // 403 Forbidden
            '404' => 0, // 404 Page Not Found
        );
    }

    public function loadStats(){

        $this->setDefaultStats();

        if( file_exists($this->statsPath) ){
            $file = file($this->statsPath);
            foreach ($file as $line_index=>$line) {
                $data = explode(':', $line);
                $vi = strtolower( trim($data[0]) );
                if( isSet($this->statsData[$vi]) and isSet($data[1]) ){
                    $vv = 1 * trim($data[1]);
                    $this->statsData[$vi] = $vv;
                }
            }
        }

        return $this->statsData;

    }

    public function clearStats(){
        $this->setDefaultStats();
        $this->saveStats();
        return $this->statsData;
    }

    public function saveStats(){
        $fp = @fopen($this->statsPath, 'wt');
        if($fp){
            foreach ($this->statsData as $key=>$value) {
                fwrite($fp, $key . ':' . $value . "\n" );
            }
        }
        // if unable to open file, please do not write anything !!!
    }

    public function adminIsLogged(){
        global $_COOKIE;
        if( !empty($_COOKIE) )
            foreach ($_COOKIE as $key=>$value)
                if( strpos($key, "wordpress_logged_in_" ) !== FALSE )
                    return TRUE;
        return FALSE;
    }

    public function save($what){
        $this->statsData[$what]++;
        $this->saveStats();
    }

    public function saveHit(){ $this->save('hit'); }
    public function save304(){ $this->save('304'); }

    public function saveMiss(){
        $settings = spClasses::get('Settings');

        if( 1 == $settings->cache_level ){
            $this->save('miss');
            return;
        }

        if( 2 == $settings->cache_level ){
            $this->save('htaccess_miss');
            return;
        }
    }

    public function saveRedirect($code){ $this->save($code); }

    public function saveDisabled(){
        if( $this->adminIsLogged() ){
            $this->save('admin');
        }else{
            $this->save('disabled');
        }
    }

    public function saveStat404(){
        $this->statsData['404']++;
        $this->saveStats();

        $r404 = spClasses::get('404s');
        $r404->saveActualUrl();
    }
    
}
  
  
?>