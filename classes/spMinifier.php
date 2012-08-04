<?php

class spMinifier{

    public $input;
    public $output;
    public $gzip;
    public $ext;
    public $exp;

    function __construct( $input="" ){
        $this->input = $input;
        $this->gzip = function_exists("gzopen");
        $this->ext = "htm";
        $this->exp = 0;
    }
    
    function setInput($input       ){ $this->input= $input; }
    function setGzip( $gzipEnabled ){ $this->gzip = $gzipEnabled AND function_exists("gzopen"); }
    function setExt(  $ext         ){ $this->ext  = $ext; }
    function setExp(  $exp         ){ $this->exp  = $exp; }

    function load( $file ){
        if( ! file_exists($file) ) die("Requested file does not exists: $file");
        if( ! is_readable($file) ) die("Cannot read requested file: $file");
        $this->input = file_get_contents($file);
    }
    
    function save($file){
        $this->saveFile( $file );
        if( $this->gzip ){
            $this->saveGzippedFile( $file );
        }
    }

    function saveFile( $file ){
        $this->createParentsDirs(dirname( $file ));
        if( empty( $this->output ) and ! empty($this->input ) ){
            $this->minify();
        }
        @file_put_contents($file, $this->output);
    }

    function saveGzippedFile( $file ){
        $this->createParentsDirs(dirname( $file ));
        if( empty( $this->output ) and ! empty($this->input ) ){
            $this->minify();
        }
        $gz = @gzopen( $file.".gz", "w9");
        if( $gz ){
            gzwrite($gz, $this->output);
            gzclose($gz);
        }
    }

    function showOutput($file = null){
        $this->showOutputHeaders( $file );

        if( ! function_exists("headers_sent") or headers_sent() ){
            die("FATAL ERROR: headers was already sent!");
            return;
        }

        if( !empty($file) ){
            if( ! file_exists($file) ){
                die("Requested file does not exist: $file");
            }
            if( $this->gzip and ! file_exists("$file.gz") ){
                $this->gzip = FALSE;
            }
            $h = getallheaders();
            if( isSet($h['If-Modified-Since']) ){
                $ft = filemtime( $file );
                if( strtotime($h['If-Modified-Since']) >= $ft ) {
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $ft ).' GMT', true, 304);
                    exit;
                }
            }
        }

        if( $this->gzip ){
            $h = getallheaders();
            if ( FALSE === strpos( strtolower($h["Accept-Encoding"]), "gzip" ) ){
                $this->showOutputNormal($file);
                return;
            }
            $this->showOutputGzip($file);
        }else{
            $this->showOutputNormal($file);
        }
    }

    private function showOutputHeaders($file){

        header("Vary: Accept-Encoding", true);
        if( !empty( $this->exp ) ){
            header( "Expires: A".$this->exp, true );
        }

        if( function_exists("header_remove") ){
            @header_remove("Link");
            @header_remove("Pragma");
            @header_remove("Server");
            @header_remove("X-Pingback");
            @header_remove("X-Powered-By");
        }

        if( empty($file) ){
            header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT", true);
        }else{
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($file)) . " GMT");
        }

        if( "htm" == $this->ext ){
            header("Cache-Control: private, must-revalidate", true);
        }else{
            header("Cache-Control: public, must-revalidate", true);
        }

        switch($this->ext){
              case "gif": header( "Content-Type: image/gif", true ); break;
              case "ico": header( "Content-Type: image/ico", true ); break;
              case "jpeg":
              case "jpg": header( "Content-Type: image/jpeg", true ); break;
              case "png": header( "Content-Type: image/png", true ); break;

              case "js":  header( "Content-Type: text/javascript; charset=utf-8", true ); break;
              case "css": header( "Content-Type: text/css; charset=utf-8", true ); break;
              case "xml": header( "Content-Type: text/xml; charset=utf-8", true ); break;
              case "html":
              case "htm": header( "Content-Type: text/html; charset=utf-8", true ); break;
              case "txt": header( "Content-Type: text/plain; charset=utf-8", true ); break;
        }
    }
    
    private function showOutputNormal($file){
        if( empty($file) ){
            header("Content-Length: ". strlen($this->output) );
            echo $this->output;
        }else{
            header("Content-Length: ". filesize($file) );
            readfile($file);
        }
    }
    
    private function showOutputGzip($file){
        if( empty($file) ){
            $this->output = gzencode($this->output, 9);
            header("Content-Length: ". strlen($this->output) );
            header("Content-Encoding: gzip");
            echo $this->output;
        }else{
            $file = "$file.gz";
            header("Content-Length: ". filesize($file) );
            header("Content-Encoding: gzip");
            readfile($file);
        }
    }

    function minify(){
        // $this->output = "ABSTRACT CLASS minifier IN minifier.php NOTHING TO MINIFY.";
        $this->output = $this->input;
    }

    function createParentsDirs( $dir ){
        $dir = $dir . DIRECTORY_SEPARATOR;
        if( "\\" == DIRECTORY_SEPARATOR ){
            $dir = str_replace("/","\\",$dir);
        }

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
    }
}