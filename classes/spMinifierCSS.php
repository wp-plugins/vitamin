<?php

require_once 'spMinifier.php';

class spMinifierCSS extends spMinifier{

    function minify(){

          $this->output = $this->input;
          $this->output = strtr($this->output, "\n\r\t", "   ");
          $this->output = preg_replace('#\s{1,}#', ' ', $this->output);
          $this->output = preg_replace('#/\*.*\*/#U', '', $this->output);
          $this->output = str_replace( array(' {',   ' }',   ' ,',   ' :',   ' ;',   ' >',),
                                       array( '{',    '}',    ',',    ':',    ';',    '>',),
                                       $this->output);
          $this->output = str_replace( array('{ ',   '} ',   ', ',   ': ',   '; ',   '> ',),
                                       array( '{',    '}',    ',',    ':',    ';',    '>',),
                                       $this->output);
          $this->output = str_replace( array(';}',   ', ',   ': ',   '; ',   '> ',),
                                       array('}',    ',',    ':',    ';',    '>',),
                                       $this->output);
    }
}
