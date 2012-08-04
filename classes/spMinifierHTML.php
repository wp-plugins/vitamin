<?php

require_once 'spMinifier.php';

class spMinifierHTML extends spMinifier{

    private function mHC(&$code){
        $code = preg_replace('#(\s{1,})#', ' ', $code);
        //$this->aC($code); return;

        for ($i=0;$i<10;$i++) {
            $code = preg_replace('#<([^\<\>]{1,}) ?= ?"([^" =<>]{1,})"#U', '<$1=$2 ', $code);
            $code = preg_replace('#<([^<>]{1,}) ?= ?\'([^\' =<>]{1,})\'#U', '<$1=$2 ', $code);
        }
        //$code = preg_replace('#<([^>]{1,})([^\/>]) />#U', '<$1$2/>', $code);
        $code = preg_replace('#<([^<>]{1,})([^\/>]) >#U', '<$1$2>', $code);

        // google minifier hates <a href=http://example.com/ >  ===> <a href=http://example.com/>
        $code = preg_replace('#<([aA])([^<>]{1,})\/ >#U', '<$1$2/>', $code);

        $code = preg_replace('#( {1,})#', ' ', $code);
        $this->aC($code);
    }
    
    private function aCC(&$code){
        $code = strtr($code, "\n\r\t", "   ");
        $code = preg_replace('#(\s{1,})#U', ' ', $code);
        if( '[if ' == strtolower( substr($code, 0, 4) ) ){
            $this->aC($code);
        }else if( ' [if ' == strtolower( substr($code, 0, 5) ) ){
            $this->aC($code);
        }else{
            $code = '';
            $this->output = substr($this->output, 0, -4);
        }
    }
    
    private function aC(&$code){
        //$this->output .= "\n\n<!-- END OF SPECIAL CODE -->\n\n";
        $this->output .= $code;
        $code = '';
    }

    function minify(){

        $this->input = str_replace("\r","",$this->input);
        
        $s = "";
        $x = "html";
        $r = "";

        for ($i = 0; $i < strlen($this->input); $i++ ) {
            $c = $this->input[$i];

            switch ($x) {
                case "html":
                              switch($c) {
                                  case " ": case "\n": case "\r": case "\t":
                                            if($s==""){$s=" ";$r.=" ";break;}
                                            $s=" "; break;
                                  case ">": if(" /"==substr($r,-2,2)){$r[strlen($r)-2]="/";$r[strlen($r)-1]=">";$s=">";break;}
                                            $s="";$r.=">";break;
                                  case "<": if("> "==substr($r,-2,2)){$r[strlen($r)-1]="<";$s="<";break;}
                                            $s="<";$r.=$c;break;
                                  case "!": if($s=="<"){$s="<!";$r.="!";break;}
                                            $s="";$r.=$c;break;
                                  case "-": if($s=="<!"){$s="<!-";$r.="-";break;}
                                            if($s=="<!-"){$s="";$r.="-";$x="<!--";$this->mHC($r);break;}
                                            $s="";$r.=$c;break;
                                  case "t": case "T":
                                            if("<scrip"==strtolower(substr($r,-6,6))){$r.="t";$x="<script";$s="";$this->mHC($r);break;}
                                  case "e": case "E":
                                            if("<pr"==strtolower(substr($r,-3,3))){$r.="e";$x="<pre";$s="";$this->mHC($r);break;}
                                  default:  $s="";$r.=$c;break;
                              } break; // html

                case "<!--":
                              $r.=$c;  // MANDATORY FOR <![endif]-->, <!--[if IE 7]> ...
                              switch($c) {
                                  case ">": if($s=="--"){$s=" ";$x="html";$this->aCC($r);break;}
                                  case "-": if($s==""){$s="-";break;}
                                            if($s=="-"){$s="--";break;}
                                            if($s=="--"){$s="--";break;}
                                  default:  $s=""; break;
                              } break; // <!--

                case "<script":
                              switch($c) {
                                  case "\n": $s="";$r.="\n";break;
                                  case ">": if("</script"==strtolower(substr($r,-8,8))){$r.=">";$x="html";$s="";$this->aC($r);break;}
                                  default:  $s="";$r.=$c;break;
                              } break; // <script

                case "<pre":
                              switch($c) {
                                  case "\n": $s="";$r.="\n";break;
                                  case ">": if("</pre"==strtolower(substr($r,-5,5))){$r.=">";$x="html";$s="";$this->aC($r);break;}
                                  default:  $s="";$r.=$c;break;
                              } break; // <pre

                default: break;
            }
        } // for ($i = 0; $i < strlen($file); $i++ )

        switch ($x) {
            case "<!--":
                break;
            case "<script":
            case "<pre":
                $this->aC($r); break;
            default:
                $this->mHC($r); break;
        }
    }
}
