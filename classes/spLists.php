<?php

class spLists{
    public $data;
    public $dataFile;

    public $columnCount;
    public $dataDefinition;

    public $error;
    public $info;

    function __construct($dataFile, $dataDefinition){
        $this->dataFile = $dataFile;
        $this->dataDefinition = $dataDefinition;
        $this->columnCount = count( $dataDefinition );
        $this->error = array();
        $this->info = array();
    }
    
    function loadAllOneColumn($data=null){
        $fileLines = @file($this->dataFile);

        foreach ($fileLines as $index=>$line) {
            $line = trim( $line );
            if( empty($line) ) continue;

            $this->data[ MD5( $line ) ] = (object) array( $this->dataDefinition[0] => $line );
        }

        return $this->data;
    }

    function loadAll($data=null){
        if( !empty($data)         ){ return ( $this->data = $data ); }
        if( !empty( $this->data ) ){ return $this->data; }

        if( ! file_exists($this->dataFile) ){
            $fp = @fopen($this->dataFile, "wt");
            if( ! $fp ){
                $this->error[] = 'Unable to open or create file <code>'.$this->dataFile.'</code>';
            }
            @chmod( $file, 0755);
            return array();
        }else{
            if( !is_readable($this->dataFile) ){
                @chmod( $file, 0755);
            }
            if( !is_readable($this->dataFile) ){
                $this->error[] = 'Unable to open and read file <code>'.$this->dataFile.'</code>';
                return array();
            }
        }

        if( 1 == $this->columnCount ){
            return $this->loadAllOneColumn();
        }

        $fileLines = @file($this->dataFile);

        foreach ($fileLines as $index=>$line) {
            $line = trim( $line );
            if( empty($line) ) continue;
            $line = explode(' -> ', $line );

            if( $this->columnCount != count($line) ) continue;

            $dataItem = array();
            foreach($this->dataDefinition as $i=>$name) {
                $dataItem[ $name ] = trim( $line[$i] );
            }

            $this->data[ MD5( $line[1] ) ] = (object) $dataItem;
        }
        
        return $this->data;
    }
    
    function prepareDataFileForWriting( $file=null ){
        if( empty($file) ){
            $file = $this->dataFile;
        }

        if( ! file_exists( $file ) ){
            $fp = @fopen( $file, "wt");
            if( ! $fp ){
                $this->error[] = 'Unable to create and write into file <code>'.$file.'</code>';
                return null;
            }else{
                return $fp;
            }
        }

        if( ! is_writable( $file ) ){
            @chmod( $file, 0755);
            if( ! is_writable( $file ) ){
                $this->error[] = 'Unable to write into file <code>'.$file.'</code>';
                return null;
            }
        }

        return @fopen( $file, "wt");
    }

    function prepareFileForWriting($file){
        if( ! file_exists( $file ) ){
            $fp = @fopen( $file, "wt");
            if( ! $fp ){
                $this->error[] = 'Unable to create and write into file <code>'.$file.'</code>';
                return null;
            }else{
                chmod($file, 0755);
                return $fp;
            }
        }

        if( ! is_writable( $file ) ){
            @chmod( $file, 0755);
            if( ! is_writable( $file ) ){
                $this->error[] = 'Unable to write into file <code>'.$file.'</code>';
                return null;
            }
        }

        return @fopen( $file, "wt");
    }

    function prepareGzipFileForWriting($file){
        if( ! file_exists( $file ) ){
            $gz = @gzopen( $file, 'w9');
            if( ! $gz ){
                $this->error[] = 'Unable to create and write into file <code>'.$file.'</code>';
                return null;
            }else{
                chmod($file, 0755);
                return $gz;
            }
        }

        if( ! is_writable( $file ) ){
            @chmod( $file, 0755);
            if( ! is_writable( $file ) ){
                $this->error[] = 'Unable to write into file <code>'.$file.'</code>';
                return null;
            }
        }

        return @gzopen( $file, 'w9');
    }

    function saveAll($fp = null){
        if( empty($fp) ) {
            if( empty($this->error) ){
                $fp = $this->prepareDataFileForWriting($this->dataFile);
            }
            if( ! empty($this->error) ){
                return FALSE;
            }
        }

        if( !empty($this->data) ){
            foreach($this->data as $name=>$datas) {
                $datas = implode(' -> ', (array) $datas );
                fwrite($fp, $datas."\n");
            }
        }
        @fclose($fp);
        return TRUE;
    }

}