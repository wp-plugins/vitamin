<?php

define('MAX_403_LIST_LENGHT', 1000);

require_once 'spLists.php';

class sp403s {

    public $uri;
    public $MD5uri;

    function __construct( ){
        $this->dataList = new spLists(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'403.txt',
            array('time','url403','eventHappened','ip')
        );

        $this->dataList->loadAll();

        $this->uri = $_SERVER["REQUEST_URI"];
        $this->MD5uri = MD5($this->uri);
    }

    public function saveActualUrl(){
        $fp = $this->dataList->prepareDataFileForWriting();
        if( empty($fp) ) return;

        $count = 1;
        
        if( !empty( $this->dataList->data[ $this->MD5uri ] ) ){
            $count = 1 + $this->dataList->data[ $this->MD5uri ]->eventHappened;
            unset( $this->dataList->data[ $this->MD5uri ] );
        }

        fwrite($fp, implode(' -> ', array( Date("Y-m-d H:i:s"), $this->uri, $count, $_SERVER['REMOTE_ADDR'] ) ) );
        fwrite($fp, "\n");

        $this->dataList->saveAll($fp);
    }
}
  
  
?>