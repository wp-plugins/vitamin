<?php

require_once 'spLists.php';

class spRedirs {

    public $data;
    public $dataFile;

    public $code;
    public $destination;
    public $uri;
    public $MD5uri;

    function __construct( ){
        $this->dataList = new spLists(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'redir.txt',
            array('priority','orgUrl','code','redirUrl')
        );

        $this->loadAll();
        
        $this->uri = $_SERVER["REQUEST_URI"];
        $this->MD5uri = MD5($this->uri);
    }

    function shouldBeApplied(){

        if( !empty($this->dataList->error) ) return FALSE;

        if( empty($this->dataList->data) ) return FALSE;
        
        foreach ($this->dataList->data as $expression=>$data) {
            if( $expression == $this->MD5uri ){
                $this->code = $this->dataList->data[ $this->MD5uri ]->code;
                $this->destination = $this->dataList->data[ $this->MD5uri ]->redirUrl;
                return TRUE;
            }else if( FALSE !== strpos($data->orgUrl, '%anything%') ) {
                $e = $data->orgUrl;
                $e = str_replace('%anything%','(.*)',$e);
                preg_match( "#$e#is", $this->uri, $match );
                if( 0 == count($match) ) continue;
                if( FALSE === strpos($data->redirUrl, '%anything%') ){
                    $this->destination = $data->redirUrl;
                }else{
                    $this->destination = $data->redirUrl;
                    foreach ($match as $key=>$value) {
                        if(0!=$key){
                            $this->destination = preg_replace('/%anything%/', $match[$key], $this->destination, 1);
                        }
                    }
                }
                $this->code = $data->code;
                return TRUE;

            }
        }
        return FALSE;
    }

    public function redirect(){
        $spStats = spClasses::get('Stats');
        $spStats->saveRedirect( $this->code );
        
        header( "Location: " . $this->destination, TRUE, $this->code);
        exit;
    }

    public function loadAll(){
        $data = $this->dataList->loadAll();

        if( empty($data) ) return;
        
        $this->dataList->data = array();
        for($i=1;$i<=10;$i++) {
            foreach ($data as $key=>$value) {
                if( $i == $value->priority ){
                    if( 403 == $value->code ){
                        $value->redirUrl = '-';
                    }
                    $this->dataList->data[$key] = $value;
                }
            }
        }
        return;
    }
    
    public function addOrReplace($d_POST){
        $fp = $this->dataList->prepareDataFileForWriting();
        if( empty($fp) ) return;

        $d_POST['orgUrl'] = stripslashes($d_POST['orgUrl']);
        $d_POST['redirUrl'] = stripslashes($d_POST['redirUrl']);

        if( $d_POST['orgUrl'] == $d_POST['redirUrl'] ){
              $this->dataList->error[] = 'Unable to save: fields "Orginal URL" and "Redirect to" are same: '.$d_POST['redirUrl'];
              return;
        }

        if( '//' == substr($d_POST['orgUrl'], 0, 2) ) $d_POST['orgUrl'] = substr($d_POST['orgUrl'], 2);
        if( 'http://' == substr($d_POST['orgUrl'], 0, 7) ) $d_POST['orgUrl'] = substr($d_POST['orgUrl'], 7);
        if( 'https://'== substr($d_POST['orgUrl'], 0, 8) ) $d_POST['orgUrl'] = substr($d_POST['orgUrl'], 8);

        if( !empty($_SERVER['SERVER_NAME']) ){
            if( 0 === strpos($d_POST['orgUrl'],$_SERVER['SERVER_NAME']) ){
                $d_POST['orgUrl'] = substr($d_POST['orgUrl'], strlen($_SERVER['SERVER_NAME']) );
            }
        }

        if( !empty( $_GET['keyToUpdate'] ) and !empty( $this->dataList->data[ $_GET['keyToUpdate'] ] ) ){
            unset( $this->dataList->data[ $_GET['keyToUpdate'] ] );
        }

        $this->dataList->data[ md5( $d_POST['orgUrl'] ) ] = (object) array(
                  'priority' => $d_POST['priority'],
                  'orgUrl' => $d_POST['orgUrl'],
                  'code' => $d_POST['code'],
                  'redirUrl' => $d_POST['redirUrl']
        );

        if( $this->dataList->saveAll($fp)){
            $this->dataList->info[] = "Item <code>".htmlentities($d_POST['orgUrl'])."</code> was added";
        }else{
            $this->dataList->error[] = "Unable to add <code>".htmlentities($d_POST['orgUrl'])."</code> item";
        }
    }
}
  
  
?>