<?php

define('MAX_404_LIST_LENGHT', 1000);

require_once 'spLists.php';

class sp404s {

    public $uri;
    public $MD5uri;

    function __construct( ){
        $this->dataList = new spLists(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'404.txt',
            array('time','url404','urlReferer','eventHappened','ip')
        );

        $this->dataList->loadAll();

        $this->uri = $_SERVER["REQUEST_URI"];
        $this->MD5uri = MD5($this->uri);
    }

    public function saveActualUrl(){
        $fp = $this->dataList->prepareDataFileForWriting();
        if( empty($fp) ) return;

        $referer = "Unknown referer";
        if( !empty($_SERVER) and !empty($_SERVER['HTTP_REFERER']) ){
            $referer = $_SERVER['HTTP_REFERER'];
        }

        $count = 1;
        
        if( !empty( $this->dataList->data[ $this->MD5uri ] ) ){
            $count = 1 + $this->dataList->data[ $this->MD5uri ]->eventHappened;
            unset( $this->dataList->data[ $this->MD5uri ] );
        }

        fwrite($fp, implode(' -> ', array( Date("Y-m-d H:i:s"), $this->uri, $referer, $count, $_SERVER['REMOTE_ADDR'] ) ) );
        fwrite($fp, "\n");

        $this->dataList->saveAll($fp);
    }
}
  
  
?>