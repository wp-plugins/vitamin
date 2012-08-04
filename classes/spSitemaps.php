<?php

function_exists ('is_admin') or exit;
is_admin()                   or exit;
defined('SP_PLUGIN_ROOT')    or exit;
defined('SP_ABSPATH')        or exit;

require_once 'spLists.php';

class spSitemaps {

    public $dataList;
    public $rootIndexSitemap;

    public $taxVisibility;
    public $visiblePostTypes;
    
    function __construct(){
        $this->SitemapDirectory = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'sitemaps'.DIRECTORY_SEPARATOR;
        $this->rootIndexSitemap = $this->SitemapDirectory.'index-sitemap.xml';
        $this->rootRobotsTXT = $this->SitemapDirectory.'robots.txt';

        $this->dataList = new spLists(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'sitemaps.txt',
            array("type","title","contains","limitation")
        );

        $this->dataList->loadAll();
    }

    function deleteObsolete(){
        if( ! is_writable( $this->SitemapDirectory ) ){
            $this->dataList->error[] = 'Unable to write into directory <code>'.$this->SitemapDirectory.'</code>';
            return;
        }

        if (is_dir($this->SitemapDirectory)) {
            if ($dh = opendir($this->SitemapDirectory)) {
                while (($file = readdir($dh)) !== false) {
                    if( ! is_file($this->SitemapDirectory . $file) ) continue;
                    if( 'index-sitemap.xml'    == $file ) continue;
                    if( 'index-sitemap.xml.gz' == $file ) continue;
                    if( 'robots.txt'           == $file ) continue;
                    if( 'robots.txt.gz'        == $file ) continue;
                    $fe = explode('.',$file);
                    if( (count( $fe ) != 2) and (count( $fe ) != 3) ){
                        @unlink( $this->SitemapDirectory . $file );
                    }
                    if( ! isSet( $this->dataList->data[ md5($fe[0]) ] ) ){
                        @unlink( $this->SitemapDirectory . $file );
                    }
                    if( ( 'text' == $this->dataList->data[ md5($fe[0]) ]->type ) and ( $fe[1] != 'txt'  ) ){
                        @unlink( $this->SitemapDirectory . $file );
                    }
                    if( ( 'text' != $this->dataList->data[ md5($fe[0]) ]->type ) and ( $fe[1] != 'xml'  ) ){
                        @unlink( $this->SitemapDirectory . $file );
                    }
                }
                closedir($dh);
            }
        }
    }

    function refreshAll( ){
        $this->updateIndexSitemap();
        $this->updateRobotsTXT();
        foreach($this->dataList->data as $name=>$description) {
            $this->updateSitemap($description);
        }
    }
    
    function updateTaxVisibility(){
        if( !empty($this->taxVisibility) ){
            return $this->taxVisibility;
        }
        // we want only public-visible taxonomies
        $taxonomies = get_taxonomies( array(), 'objects', 'and' );
        $this->taxVisibility = array();
        foreach ($taxonomies as $key=>$tax_obj) {
            $this->taxVisibility[ ''.$tax_obj->name ] = 1*$tax_obj->public;
        }
        return $this->taxVisibility;
    }
    
    function updatevisiblePostTypes(){
        if( !empty($this->visiblePostTypes) ){
            return $this->visiblePostTypes;
        }
        $this->visiblePostTypes = get_post_types(array('public'=>true),'names','and');
        unset($this->visiblePostTypes['attachment']);
        return $this->visiblePostTypes;
    }

    function getTaxonomyData($description){
        $this->updateTaxVisibility();

        global $wpdb;

        $Q = " SELECT t.slug , tt.taxonomy
               FROM " . $wpdb->prefix . "term_taxonomy tt,  " . $wpdb->prefix . "terms t
               WHERE tt.term_id = t.term_id
               AND 1=1
               ORDER BY tt.taxonomy, t.slug
               ";

        if( 'tax_all' != $description->contains ) {
            $Q = str_replace("1=1", " taxonomy = '" . substr($description->contains, 4) . "' ", $Q );
        }

        $ret = array();
        $terms = $wpdb->get_results($Q);

        foreach ($terms as $i=>$row) {
            if( !empty($this->taxVisibility[ $row->taxonomy ] ) ) {
                $ret[] = get_term_link( $row->slug, $row->taxonomy );
            }
        }
        return $ret;
    }
    
    function getPostLikeData($description){
        global $wpdb;
        
        $contains = substr($description->contains,5);

        $this->updatevisiblePostTypes();
        $postTypeQ = ' ( 0 ';
        foreach ($this->visiblePostTypes as $name=>$slug) {
            $postTypeQ.= " OR p.post_type = '$slug' ";
        }
        $postTypeQ.= ' ) ';

        if( empty( $description->limitation ) or ( FALSE !== strpos( '|'.$description->limitation.'|', '|0|' ) ) ){
            $Q = " SELECT p.ID " .
                 " FROM `" . $wpdb->prefix . "posts` p " .
                 " WHERE `post_status` = 'publish' " .
                 " AND `post_date` < NOW() " .
                 ( "all" != $contains ? " AND `post_type` = '$contains' " : " " ).
                 " AND " . $postTypeQ ;
        } else {
            $postLimitation = ' ( 0 ';
            foreach ( explode('|',$description->limitation) as $i=>$lim) {
                $postLimitation.= " OR t.`term_taxonomy_id` = $lim ";
            }
            $postLimitation.= ' ) ';
            $Q = " SELECT p.ID " .
                 " FROM " . $wpdb->prefix . "posts p, " . $wpdb->prefix . "term_relationships t " .
                 " WHERE `post_status` = 'publish' " .
                 " AND `post_date` < NOW() " .
                 ( "all" != $contains ? " AND `post_type` = '$contains' " : " " ) .
                 " AND `object_id` = `ID` " .
                 " AND " . $postLimitation .
                 " AND " . $postTypeQ ;
        }

        $posts = $wpdb->get_results($Q);

        $ret = array();

        foreach ( $posts as $key=>$one_post ) {
            $ret[ $one_post->ID ] = get_permalink( $one_post->ID );
        }

        return $ret;
    }

    function getImages($description){
        $links = $this->getPostLikeData($description);
        $imagesData = array();

        $trans = array('&'=>'&amp;','<'=>'&lt;','>'=>'&gt;');

        foreach ( $links as $ID=>$link ) {

            $images = get_posts( array( 'post_type' => 'attachment', 'post_parent' => $ID, 'post_mime_type' => 'image', ) );
            if( empty($images) ) continue;
            $imagesData[ $ID ] = array();
          	foreach ($images as $img) {
                $imdData = array();
                $imdData['loc'] = wp_get_attachment_image_src( $img->ID, 'full' );
                $imdData['loc'] = $imdData['loc'][0];
                $alt = get_post_meta( $img->ID, '_wp_attachment_image_alt', true);
                if(!empty($alt)) {
                    $imdData['title'] = strtr( $alt, $trans );
                }
                $imagesData[ $ID ][] = (object) $imdData;
            }
        }

        return (object) array( 'links' => $links, 'imagesData' => $imagesData );
    }

    function saveTextSitemap($name, $links){
        $fp = $this->dataList->prepareFileForWriting( $this->SitemapDirectory . $name . '.txt' );
        if( ! $fp ){ return; }

        $gz = $this->dataList->prepareGzipFileForWriting( $this->SitemapDirectory . $name . '.txt.gz' );
        if( ! $gz ){ return; }

        $output = '';
        $site_url = site_url();

        foreach ($links as $index=>$pagelink) {
            if( $site_url != substr($pagelink, 0, strlen($site_url) ) ){
                // This should fix multisites, but dunno
                continue;
            }
            $output = $output . $pagelink . "\n" ;
        }

        fwrite($fp, $output);
        fclose($fp);

        gzwrite($gz, $output);
        gzclose($gz);
    }

    function saveNormalSitemap($name, $links){
        $fp = $this->dataList->prepareFileForWriting( $this->SitemapDirectory . $name . '.xml' );
        if( ! $fp ){ return; }

        $gz = $this->dataList->prepareGzipFileForWriting( $this->SitemapDirectory . $name . '.xml.gz' );
        if( ! $gz ){ return; }

        $output = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
        $output.= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $site_url = site_url();

        foreach ($links as $index=>$pagelink) {
            if( $site_url != substr($pagelink, 0, strlen($site_url) ) ){
                // This should fix multisites, but dunno
                continue;
            }
            $output .= '<url><loc>' . $pagelink . '</loc></url>';
        }
        $output.= '</urlset>';

        fwrite($fp, $output);
        fclose($fp);

        gzwrite($gz, $output);
        gzclose($gz);
    }

    function saveImageSitemap($name, $data){
        $fp = $this->dataList->prepareFileForWriting( $this->SitemapDirectory . $name . '.xml' );
        if( ! $fp ){ return; }

        $gz = $this->dataList->prepareGzipFileForWriting( $this->SitemapDirectory . $name . '.xml.gz' );
        if( ! $gz ){ return; }

        $output = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'.
                  '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '.
                  'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        foreach($data->links as $index=>$value) {
            if( empty($data->imagesData[$index]) ) continue;
            $output.= '<url>';
            $output.= '<loc>' . $value . '</loc>';
            $output.= "<image:image>" ;
            foreach($data->imagesData[$index] as $key=>$image) {
                $output.= "<image:loc>" . $image->loc . "</image:loc>" ;
                if( !empty($image->title) ) {
              		$output.= "<image:title>" . $image->title . "</image:title>" ;
              		$output.= "<image:caption>" . $image->title . "</image:caption>" ;
                }
            }
            $output.= "</image:image>" ;
            $output.= '</url>';
        }
        $output.= '</urlset>';

        fwrite($fp, $output);
        fclose($fp);

        gzwrite($gz, $output);
        gzclose($gz);
    }

    function updateSitemap($description){
        if( 'tax_' == substr($description->contains,0,4) ){
            if( 'text' == $description->type ){
                $this->saveTextSitemap( $description->title, $this->getTaxonomyData($description) );
            }else if( 'normal' == $description->type ){
                $this->saveNormalSitemap( $description->title, $this->getTaxonomyData($description) );
            }else{
                $this->dataList->error[] = 'Unkwnown type '.$description->type.' of sitemap '.$description->title;
            }
        }else if( 'post_' == substr($description->contains,0,5) ){
            if( 'text' == $description->type ){
                $this->saveTextSitemap( $description->title, $this->getPostLikeData($description) );
            }else if( 'normal' == $description->type ){
                $this->saveNormalSitemap( $description->title, $this->getPostLikeData($description) );
            }else if( 'image' == $description->type ){
                $this->saveImageSitemap( $description->title, $this->getImages($description) );
            }else{
                $this->dataList->error[] = 'Unkwnown type '.$description->type.' of sitemap '.$description->title;
            }
        }else{
            $this->dataList->error[] = 'Unkwnown type '.$description->contains;
            return;
        }
    }

    function updateRobotsTXT( ){
        if( 1 == get_option( 'sp_file_robots_txt_enable', 0 ) ) {
            return;
        }

        $fp = $this->dataList->prepareFileForWriting( $this->rootRobotsTXT );
        if( ! $fp ){ return; }

        $gz = $this->dataList->prepareGzipFileForWriting( $this->rootRobotsTXT.'.gz' );
        if( ! $gz ){ return; }

        $site_url = site_url();
        $output = "User-agent: *\n";

        if( ! empty( $this->dataList->data ) ){
            foreach ($this->dataList->data as $md5title=>$sitemap_data) {
                $output .= "Sitemap: $site_url/sitemaps/".$sitemap_data->title.( ( 'text' == $sitemap_data->type ) ? ".txt" : ".xml")."\n";
            }
        }
        
        if( get_option( 'sp_add_rss_to_robots_sitemap', 1 ) ){
            $output .= "Sitemap: $site_url/feed/\n";
        }

        if( get_option( 'sp_add_atom_to_robots_sitemap', 1 ) and get_option( 'enable_app', 1 ) ){
            $output .= "Sitemap: $site_url/feed/atom/\n";
        }

        fwrite($fp, $output);
        fclose($fp);

        gzwrite($gz, $output);
        gzclose($gz);
    }

    function updateIndexSitemap( ){
        if( 1 == get_option( 'sp_file_root_sitemap_xml_enable', 0 ) ) {
            return;
        }

        $fp = $this->dataList->prepareFileForWriting( $this->rootIndexSitemap );
        if( ! $fp ){ return; }

        $gz = $this->dataList->prepareGzipFileForWriting( $this->rootIndexSitemap.'.gz' );
        if( ! $gz ){ return; }
        
        $site_url = site_url();
        $output = '';
        $output .= '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."";
        $output .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."";

        if( ! empty( $this->dataList->data ) ){
            foreach ($this->dataList->data as $md5title=>$sitemap_data) {
                $output .= "<sitemap><loc>$site_url/sitemaps/" .$sitemap_data->title;
                if( 'text' == $sitemap_data->type ){
                    $output .= ".txt";
                }else{
                    $output .= ".xml";
                }
                $output .= "</loc></sitemap>";
            }
        }

        if( get_option( 'sp_add_rss_to_robots_sitemap', 1 ) ){
            $output .= "<sitemap><loc>$site_url/feed/</loc></sitemap>";
        }

        if( get_option( 'sp_add_atom_to_robots_sitemap', 1 ) and get_option( 'enable_app', 1 ) ){
            $output .= "<sitemap><loc>$site_url/feed/atom/</loc></sitemap>";
        }
        $output .= "</sitemapindex>";

        fwrite($fp, $output);
        fclose($fp);

        gzwrite($gz, $output);
        gzclose($gz);
    }
}
  
  
?>