<?php

if( !function_exists ('is_admin') or !is_admin() ){ exit; }

class spPHPInfoAdmin{

    function printAdminPage(){
        ob_start();
        phpinfo();
        $data = ob_get_contents();
        ob_end_clean();
        $pos = strpos($data,'<style');
        if( FALSE !== $pos ){
            $css = substr($data, 6 + $pos);
            $css = substr($css,  1 + strpos($css,'>') );
            $css = substr($css,0,    strpos($css,'</style') );
            $css = str_replace(' font-size: 75%;','',$css);
            echo "\n\n<style type='text/css'>";
            echo "\n\n/* phpinfo() css */\n";
            echo "\n#sp_phpinfo{padding-top:30px}\n";
            echo "\n#sp_phpinfo .php_info_table{width:99%;overflow:auto}\n";
            echo "\n#sp_phpinfo .e{white-space: pre;}\n";
            echo "\n#sp_phpinfo .v{word-wrap: break-word;word-break: break-all;}\n";
            $css = explode("}", $css);
            foreach ($css as $line_index=>$line) {
                $line = trim($line);
                $rule = explode('{',$line);
                $tags = explode(',',$rule[0]);
                if( 0 == count($tags) ) continue;
                foreach($tags as $tag_index=>$tag) {
                    $tag = trim($tag);
                    if( 0 != $tag_index ) echo ",\n";
                    echo '#sp_phpinfo ';
                    if( 'body' == $tag ) continue;
                    echo $tag;
                }
                echo "{";
                echo $rule[1];
                echo "}\n";
            }
            echo "</style>\n\n";
        }
        echo '<div id="sp_phpinfo">';
        $pos = strpos($data,'<body');
        if( FALSE !== $pos ){
            $data = substr($data, 5 + $pos);
            $data = substr($data, 1 + strpos($data,'>') );
            $data = substr($data, 0,  strpos($data,'</body') );
            $data = str_replace(array('<table','</table>','width="600"'),array('<div class="php_info_table"><table','</table></div>','style="min-width:600px"'),$data);
            echo $data;
        }
        echo '</div>';
    }

}
