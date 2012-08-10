<?php

if( !function_exists ('is_admin') or !is_admin() ){ exit; }

class spHtaccessAdmin{
    public $settings;
    public $redirData;
    public $blocksData;

    public $pathHtaccess;
    public $homeRoot;
    public $homeURL;
    public $pluginRoot;
    public $cacheDirPartByURL;

    public $isReadable;
    public $inContent;

    public $isWritable;
    public $outContent;

    function printAdminPage(){
        $this->refreshHtaccess( FALSE );
        echo '<h1>outContent</h1><pre>';print_r($this->outContent);echo '</pre>';
        echo '<h1>redirData</h1><pre>';print_r($this->redirData);echo '</pre>';
    }

    function __construct(){
        $this->pathHtaccess = SP_ABSPATH.'.htaccess';

        $this->homeURL = home_url();
        
        $this->homeRoot = parse_url(home_url());
        $this->homeRoot = isset( $this->homeRoot['path'] ) ?
                          trailingslashit($this->homeRoot['path']) :
                          '/' ;

        $this->pluginRoot = parse_url(SP_PLUGIN_URL);
        $this->pluginRoot = isset( $this->pluginRoot['path'] ) ?
                            trailingslashit($this->pluginRoot['path']) :
                            '/' ;

        $this->isReadable = is_readable($this->pathHtaccess);
        $this->isWritable = is_writable($this->pathHtaccess);
        
        $this->settings = spClasses::get('Settings');

        $this->cacheDirPartByURL = site_url();
        if( 'https://' == substr($this->cacheDirPartByURL,0,8) ) $this->cacheDirPartByURL = substr($this->cacheDirPartByURL,8);
        if( 'http://'  == substr($this->cacheDirPartByURL,0,7) ) $this->cacheDirPartByURL = substr($this->cacheDirPartByURL,7);
        if( 'www.'     == substr($this->cacheDirPartByURL,0,4) ) $this->cacheDirPartByURL = substr($this->cacheDirPartByURL,4);
    }

    function loadRedirs(){
        if( !empty($this->redirData) ) return;
        if( is_array($this->redirData) ) return;

        // DO NOT DELETE => RECURSION FAIL
        $this->redirData = array();

        $r = spClasses::get('Redirs');
        $r = $r->dataList->data;

        if( empty($r) ) return;
        
        foreach($r as $key=>$value)
            if( 1 == $value->priority ) $this->redirData[$key]=$value;
    }

    function loadBlocks(){
        if( !empty($this->blocksData) ) return;
        if( is_array($this->blocksData) ) return;

        // DO NOT DELETE => RECURSION FAIL
        $this->blocksData = array();

        $b = spClasses::get('Blocks');
        $b = $b->dataList->data;
        if( empty($b) ) return;
        foreach($b as $key=>$value) $this->blocksData[$key]=$value;
    }

    function refreshHtaccess($add_plugin_code=TRUE){
        if( file_exists( $this->pathHtaccess ) ){
            if( ! $this->isReadable ) return FALSE;
            if( ! $this->isWritable ) return FALSE;
            $this->inContent = file_get_contents($this->pathHtaccess);
            if( FALSE === strpos($this->inContent,'# BEGIN WordPress') ){
                // If there is no WP stuff, just load default
                $this->inContent = $this->getDefaultHtaccess();
            }
        }else{
            $this->inContent = $this->getDefaultHtaccess();
        }

        // Windows apache FIX
        $this->inContent = str_replace("\r","",$this->inContent);

        $this->loadRedirs();
        $this->loadBlocks();

        if( $add_plugin_code ){
            $this->outContent = $this->getTextFromPStoHtaccess() . $this->removeSPText($this->inContent);
        }else{
            $this->outContent = $this->removeSPText($this->inContent);
        }

        $fp = @fopen($this->pathHtaccess,"wt");
        if( ! $fp ){ return FALSE; }
        fwrite($fp,$this->outContent);
        fclose($fp);

        return TRUE;
    }

    function removeSPText($input){
        $pos = strpos($input,"# BEGIN SP\n");

        if( FALSE === $pos ){
            return $input;
        }

        $ret = substr($input, 0, $pos);

        $pos = strpos($input,"# END SP\n");

        if( FALSE === $pos ){
            return $this->getDefaultHtaccess();
        }
        
        $ret = trim($ret) . trim(substr($input, $pos + strlen("# END SP\n") ));

        return $ret;
    }
    
    function getDefaultHtaccess(){
        return
            "# BEGIN WordPress\n".
            "<IfModule mod_rewrite.c>\n".
            "RewriteEngine On\n".
            "RewriteBase $this->homeRoot\n".
            "RewriteRule ^index\.php$ - [L]\n".
            "RewriteCond %{REQUEST_FILENAME} !-f\n".
            "RewriteCond %{REQUEST_FILENAME} !-d\n".
            "RewriteRule . $this->homeRoot"."index.php [L]\n".
            "</IfModule>\n".
            "\n".
            "# END WordPress\n".
            "";
    }
    
    function getTextFromPStoHtaccess(){
        return
            "# BEGIN SP\n".
            "AddDefaultCharset UTF-8\n".
            "DefaultLanguage ".get_bloginfo('language')."\n".
            "RewriteEngine On\n".
            "Options +FollowSymLinks\n".
            "\n### Care about comments:\n".
            "RewriteCond %{REQUEST_URI} ^".$this->homeRoot."wp-comments-post.php$\n".
            "RewriteRule (.*) ".$this->homeRoot." [L]\n".
            $this->htaccess_Blocks().
            $this->htaccess_Redirs().
            $this->htaccess_ArchivesRedirs().
            $this->htaccess_AuthorRedirs().
            $this->htaccess_ForbiddenFeeds().
            $this->htaccess_Sitemaps().
            $this->htaccess_htaccessFast404().
            $this->htaccess_htaccessCachePart().
            $this->htaccess_htaccessMinifyPart().
            $this->htaccess_addModHeadersOff().
            $this->htaccess_addModHeadersOn().
            "# END SP\n\n".
            "";
    }
    
    function htaccess_Blocks(){
        if ( 0 == count( $this->blocksData ) ) {
            return
                "\n### htaccess blocks: OFF\n\n";
        }else{
            $ret = "\n### htaccess blocks is ON - ".count( $this->blocksData )." Item(s):\n";
            $REQUEST_URI = array();
            $QUERY_STRING = array();
            foreach ( $this->blocksData as $key=>$data) {
                $url = trim($data->orgUrl);
                if(empty($url)) continue;
                $type = "REQUEST_URI";
                if( '?' == $url[0] ){
                    $url = substr($url, 1);
                    $type = "QUERY_STRING";
                }
                $url = str_replace('\\','\\\\',$url);
                $url = str_replace(
                    array( '"',   "'",   '.',  '^',  '$',  '?',  '*',  '(',  ')',  '{',  '}',  '[',  ']',  ),
                    array( '\\"', "\\'", '\.', '\^', '\$', '\?', '\*', '\(', '\)', '\{', '\}', '\[', '\]', ),
                    $url );
                $url = str_replace('%anything%','(.*)',$url);

                if( "REQUEST_URI" == $type ){
                    $REQUEST_URI[] = $url;
                }else{
                    $QUERY_STRING[] = $url;
                }
            }

            if( !empty($REQUEST_URI) ){
                $ret.= "RewriteCond %{REQUEST_URI} ^(". implode("|",$REQUEST_URI) .")$ [NC]\n";
                $ret.= "RewriteRule (.*) $this->pluginRoot"."fast403.php [L]\n";
            }
            if( !empty($QUERY_STRING) ){
                $ret.= "RewriteCond %{QUERY_STRING} ^(". implode("|",$QUERY_STRING) .")$ [NC]\n";
                $ret.= "RewriteRule (.*) $this->pluginRoot"."fast403.php [L]\n";
            }
            return $ret;
        }
    }

    function htaccess_Redirs(){
        if ( 0 == count( $this->redirData ) ) {
            return
                "\n### htaccess redirections: OFF\n";
        }else{
            $ret = "\n### htaccess redirections is ON - ".count( $this->redirData )." Item(s):\n\n";
            foreach ( $this->redirData as $key=>$data) {
                $url = $data->orgUrl;
                $url = str_replace('\\','\\\\',$url);
                $url = str_replace('.','\.',$url);
                $url = str_replace('^','\^',$url);
                $url = str_replace('$','\$',$url);
                $url = str_replace('(','\(',$url);
                $url = str_replace(')','\)',$url);
                $url = str_replace('%anything%','(.*)',$url);

                $ret.= "RewriteCond %{REQUEST_URI} !^".$this->homeRoot."wp-admin/\n";
                // RewriteCond %{REQUEST_URI} ^(.*)nofollow$
                $_orgUrl = $data->orgUrl;
                for($i=1; FALSE !== ($pos = strpos($_orgUrl,'%anything%') ); $i++ ){
                    $_orgUrl = substr($_orgUrl,0,$pos).
                               '(.*)'.
                               substr($_orgUrl,$pos+10);
                }
                $ret.= "RewriteCond %{REQUEST_URI} ^".$_orgUrl."$ [NC]\n";
                $ret.= "RewriteRule ^".$_orgUrl."$ ".$this->homeURL."/";
                $_redirUrl = $data->redirUrl;
                for($i=1; FALSE !== ($pos = strpos($_redirUrl,'%anything%') ); $i++ ){
                    $_redirUrl = substr($_redirUrl,0,$pos).
                                 '$'.$i.
                                 substr($_redirUrl,$pos+10);
                }
                $ret.= "$_redirUrl [R=".$data->code.",L]\n";
            }
            return $ret;
        }
    }
    
    function htaccess_ArchivesRedirs(){
        if( empty($this->settings->forbidden_day) ) return "\n### htaccess day archives redirections: OFF\n";

        if( 'fast404' == $this->settings->forbidden_day) {
            $ret = "\n### htaccess day archives redirections: fast 404 :\n";
            $ret.= "RewriteCond %{REQUEST_URI} ^".$this->homeRoot."([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(.*)$\n";
            $ret.= "RewriteRule ^(.*) $this->pluginRoot"."fast404.php?type=page [L]\n";
            return $ret;
        }
        
        if( 'month' == $this->settings->forbidden_day ){
            $ret = "\n### htaccess day archives redirections: redirect to month archive :\n";
            $ret.= "RewriteCond %{REQUEST_URI} ^".$this->homeRoot."([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(.*)$\n";
            $ret.= "RewriteRule ^([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(.*) ".$this->homeRoot."$1/$2/  [R=301,L]\n";
            return $ret;
        }
        
        return '';
    }

    function htaccess_AuthorRedirs(){
        if( empty($this->settings->forbidden_author) ) return "\n### htaccess author redirections: OFF\n";

        if( 'fast404' == $this->settings->forbidden_author) {
            $ret = "\n### htaccess author redirections: fast 404 :\n";
            $ret.= "RewriteCond %{REQUEST_URI} ^".$this->homeRoot."author/(.*)$\n";
            $ret.= "RewriteRule ^(.*) $this->pluginRoot"."fast404.php?type=page [L]\n";
            return $ret;
        }

        if( 'home' == $this->settings->forbidden_author ){
            $ret = "\n### htaccess author redirections: redirect to home :\n";
            $ret.= "RewriteCond %{REQUEST_URI} ^".$this->homeRoot."author/(.*)$\n";
            $ret.= "RewriteRule (.*) ".$this->homeRoot." [R=301,L]\n";
            return $ret;
        }

        return '';
    }

    function htaccess_ForbiddenFeeds(){
        if( 'fast404' != $this->settings->forbidden_special_feeds) return "\n### htaccess day, author, attachement, search feeds : OFF\n";
        
        $ret = "\n### Fast 404 for search, author and day feeds :\n";
        $ret.= "RewriteCond %{REQUEST_URI} ^".$this->homeRoot."search/(.*)/feed/(.*)$ [OR]\n";
        $ret.= "RewriteCond %{REQUEST_URI} ^".$this->homeRoot."author/(.*)/feed/(.*)$ [OR]\n";
        $ret.= "RewriteCond %{REQUEST_URI} ^".$this->homeRoot."([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed(.*)$\n";
        $ret.= "RewriteRule ^(.*) $this->pluginRoot"."fast404.php?type=search_feed [L]\n";

        return $ret;
    }

    function htaccess_Sitemaps(){
        if( empty( $this->settings->sitemaps_enabled ) ){
            return "\n### Sitemaps and robots DISABLED\n";
        }

        return
            "\n### Redirect to sitemaps and robots\n".

            // index sitemap
            "RewriteCond %{REQUEST_URI} ^$this->homeRoot"."sitemap\.xml$\n".
            "RewriteRule (.*) $this->pluginRoot"."sitemaps/index-sitemap.xml [L]\n".

            // robots.txt
            "RewriteCond %{REQUEST_URI} ^$this->homeRoot"."robots\.txt$\n".
            "RewriteRule (.*) $this->pluginRoot"."sitemaps/robots.txt [L]\n".

            "RewriteCond %{REQUEST_URI} ^$this->homeRoot"."sitemaps/(.*)$\n".
            "RewriteRule ^sitemaps/(.*) $this->pluginRoot"."sitemaps/$1 [L]\n".
            "";
    }

    function htaccess_htaccessFast404(){
        if ( 0 == count( $this->settings->fast404 ) ) {
            return
                "\n### htaccess fast 404 for files is: OFF\n";
        }else{
            $ret = "\n### htaccess fast 404 for files is ON - ".count( $this->settings->fast404 )." Item(s):\n\n";

            $ext = implode("|\\.",$this->settings->fast404);

            $ret.= "RewriteCond %{REQUEST_FILENAME} !-f\n";
            $ret.= "RewriteCond %{REQUEST_URI} ^(.*)/wp-content/(.*)(\.$ext)$ [NC]\n";
            $ret.= "RewriteRule ^(.*) $this->pluginRoot"."fast404.php [L]\n";
            $ret.= "\n";
            return $ret;
        }
    }
    
    function htaccess_htaccessCachePart(){
        if ( $this->settings->cache_level != 2 ) {
            return
                "\n### htaccess cache is: OFF\n";
        }else{
            return
                "\n".
                "### htaccess cache is: ON\n".
                "RewriteCond %{REQUEST_METHOD} !^POST$\n".
                "RewriteCond %{REQUEST_FILENAME} !-f\n".
                "RewriteCond %{REQUEST_FILENAME} !-d\n".
                "RewriteCond %{QUERY_STRING} ^$\n".
                "RewriteCond %{REQUEST_URI} ^$this->homeRoot(.*)/$\n".
                "RewriteCond %{DOCUMENT_ROOT}$this->homeRoot"."wp-content/cache/".$this->cacheDirPartByURL."/%1_c.htm -f\n".
                "RewriteRule ^(.*)/$ wp-content/cache/".$this->cacheDirPartByURL."/\$1_c.htm [L]\n".
                "\n".
                "RewriteCond %{REQUEST_METHOD} !^POST$\n".
                "RewriteCond %{QUERY_STRING} ^$\n".
                "RewriteCond %{REQUEST_URI} ^$this->homeRoot$\n".
                "RewriteCond %{DOCUMENT_ROOT}$this->homeRoot"."wp-content/cache/".$this->cacheDirPartByURL."_c.htm -f\n".
                "RewriteRule ^$ wp-content/cache/".$this->cacheDirPartByURL."_c.htm [L]\n";
        }
    }
    
    function htaccess_htaccessMinifyPart(){
        if ( empty($this->settings->mod_gzip_ext) ) {
            return
                "\n### Minification and gzip is: OFF\n";
        }else{
            $arr = implode('|',$this->settings->mod_gzip_ext);
            $arr = str_replace('|htm|', '|', $arr);
            $arr = str_replace('htm|', '', $arr);
            $arr = str_replace('|htm', '', $arr);
            $arr = str_replace('htm', '', $arr);
            if( empty($arr) ){
                return
                    "\n### Minification and gzip is: ON - only HTML\n";
            }

            // If file exists in cache => than it is minified
            $ret =
                "\n### Minification is: ON\n".
                "RewriteCond %{REQUEST_FILENAME} -f\n".
                "RewriteCond %{REQUEST_URI} ^$this->homeRoot"."wp-content/(themes|plugins)/(.*)$\n".
                "RewriteCond %{DOCUMENT_ROOT}$this->homeRoot"."wp-content/cache/".$this->cacheDirPartByURL."/wp-content/%1/%2 -f\n".
                "RewriteRule ^wp-content/(themes|plugins)/(.*)$ $this->homeRoot"."wp-content/cache/".$this->cacheDirPartByURL."/wp-content/$1/$2 [L]\n".
                "\n";
            foreach ( explode('|', $arr) as $key=>$ext) {
                $ret .=                                          //?mod=gzipexpires&exp=604800
                    "# Minification for $ext files\n".
                    "RewriteCond %{REQUEST_FILENAME} -f\n".
                    "RewriteCond %{REQUEST_URI} ^$this->homeRoot"."wp-content/(themes|plugins)/(.*)\.$ext$\n".
                    "RewriteRule ^wp-content/(themes|plugins)/(.*)\.$ext$ $this->pluginRoot"."minify.php?ext=$ext&gzip=";
                $ret .= in_array($ext, $this->settings->mod_gzip_ext) ? "true" : "false";
                if( in_array($ext, $this->settings->mod_expires_ext) ){
                    $ret .= "&exp=".$this->settings->mod_expires_time;
                }else{
                    $ret .= "&exp=0";
                }
                $ret .= "&path=$1/$2.$ext [L]\n";
            }
            return $ret;
        }
    }

    function htaccess_addModHeadersOn(){
        $ret =
            "\n### Headers gzip, expires\n".
            "<IfModule mod_headers.c>\n";

        if( ! empty( $this->settings->mod_expires_time ) ) {
            $ret .=
                "\t\t<filesMatch .*\\.(". implode( "|", $this->settings->mod_expires_ext ) .")(\\.gz)?$>\n".
                "\t\t\t\tHeader append Cache-Control \"public\"\n".
                "\t\t\t\tHeader append Expires \"A".$this->settings->mod_expires_time."\"\n".
                "\t\t</filesMatch>\n\n";
        }

        $ret .=
            "\t\tHeader unset Accept-Ranges\n".
            "\t\tHeader unset ETag\n".
            "\t\tHeader unset Link\n".
            "\t\tHeader unset Pragma\n".
            "\t\tHeader unset Transfer-Encoding\n".
            "\t\tHeader unset X-Pingback\n".
            "\t\tHeader append Vary Accept-Encoding\n".
            "\n".
            //"\t\tAddEncoding gzip .gz\n".
            "\t\tRewriteCond %{HTTP:Accept-encoding} gzip\n".
            "\t\tRewriteCond %{REQUEST_FILENAME}.gz -f\n".
            "\t\tRewriteRule ^(.*)$ $1.gz [L]\n".
            "\n".
            "\t\t<FilesMatch .*\.gz$>\n".
            "\t\t\t\tHeader set Content-Encoding: gzip\n".
            "\t\t</FilesMatch>\n";

        if( in_array('css', $this->settings->mod_gzip_ext) )
            $ret .=
            "\t\t<FilesMatch .*\.css.gz$>\n".
            "\t\t\t\tForceType text/css\n".
            "\t\t</FilesMatch>\n";

        if( in_array('htm', $this->settings->mod_gzip_ext) )
            $ret .=
            "\t\t<FilesMatch .*\.html?.gz$>\n".
            "\t\t\t\tForceType text/html\n".
            "\t\t\t\tHeader unset Expires\n".
            "\t\t</FilesMatch>\n";

        if( in_array('js', $this->settings->mod_gzip_ext) )
            $ret .=
            "\t\t<FilesMatch .*\.js.gz$>\n".
            "\t\t\t\t# Works in IE8 and older but obsolete - today standart = application/javascript\n".
            "\t\t\t\tForceType text/javascript\n".
            "\t\t</FilesMatch>\n";

        if( in_array('xml', $this->settings->mod_gzip_ext) )
            $ret .=
            "\t\t<FilesMatch .*\.xml.gz$>\n".
            "\t\t\t\tForceType text/xml\n".
            "\t\t</FilesMatch>\n";

        if( in_array('txt', $this->settings->mod_gzip_ext) )
            $ret .=
            "\t\t<FilesMatch .*\.txt.gz$>\n".
            "\t\t\t\tForceType text/plain\n".
            "\t\t</FilesMatch>\n";

        $ret .=
            "</IfModule>\n";

        return $ret;
    }

    function htaccess_addModHeadersOff(){
        if( empty( $this->settings->mod_expires_ext ) and  empty( $this->settings->mod_gzip_ext ) ) {
            return "\n### No need for adding headers by PHP when mod_headers is off\n";
        }
        
        $me = array();
        $mg = array();
        $mge = array();

        if( empty( $this->settings->mod_expires_time ) ){
            $mg = $this->settings->mod_gzip_ext;
        }else{
            foreach ($this->settings->mod_expires_ext as $key=>$value) {
                if( in_array($value, $this->settings->mod_gzip_ext) ){
                    $mge[] = $value;
                }else{
                    $me[] = $value;
                }
            }

            foreach ($this->settings->mod_gzip_ext as $key=>$value) {
                if( ! in_array($value, $this->settings->mod_expires_ext) ){
                    $mg[] = $value;
                }
            }
        }
        
        $ret = '';
        if( !empty( $me ) ){
            $ret.=
                "\t\tRewriteCond %{REQUEST_URI} ^$this->homeRoot"."wp-content/(themes|plugins|uploads|cache)/(.*)\.(". implode( "|", $me ) .")$ [NC]\n".
                "\t\tRewriteRule ^wp-content/(themes|plugins|uploads|cache)/(.*)\.(". implode( "|", $me ) .")$ $this->pluginRoot".
                "add_headers.php?ext=$3&gzip=false&exp=".$this->settings->mod_expires_time."&path=$1/$2.$3 [L]\n";
        }
        if( !empty( $mg ) ){
            $ret.=
                "\t\tRewriteCond %{REQUEST_URI} ^$this->homeRoot"."wp-content/(themes|plugins|uploads|cache)/(.*)\.(". implode( "|", $mg ) .")$ [NC]\n".
                "\t\tRewriteRule ^wp-content/(themes|plugins|uploads|cache)/(.*)\.(". implode( "|", $mg ) .")$ $this->pluginRoot".
                "add_headers.php?ext=$3&gzip=true&exp=0&path=$1/$2.$3 [L]\n";
        }
        if( !empty( $mge ) ){
            $ret.=
                "\t\tRewriteCond %{REQUEST_URI} ^$this->homeRoot"."wp-content/(themes|plugins|uploads|cache)/(.*)\.(". implode( "|", $mge ) .")$ [NC]\n".
                "\t\tRewriteRule ^wp-content/(themes|plugins|uploads|cache)/(.*)\.(". implode( "|", $mge ) .")$ $this->pluginRoot".
                "add_headers.php?ext=$3&gzip=true&exp=".$this->settings->mod_expires_time."&path=$1/$2.$3 [L]\n";
        }
        
        return
            "\n### Headers for expiration if mod_headers or mod_expires is off\n".
            "<IfModule !mod_headers.c>\n".
            $ret.
            "</IfModule>\n";
    }
}
