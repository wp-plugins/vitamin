<?php

require_once 'spSitemaps.php';
require_once 'spListsAdmin.php';

class spSitemapsAdmin extends spSitemaps {

    public $error;
    public $typeTitles;
    public $taxonomiesTypes;
    public $postTypes;

    function __construct( ){
        $this->SitemapDirectory = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'sitemaps'.DIRECTORY_SEPARATOR;
        $this->rootIndexSitemap = $this->SitemapDirectory.'index-sitemap.xml';
        $this->rootRobotsTXT = $this->SitemapDirectory.'robots.txt';

        $this->dataList = new spListsAdmin(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'sitemaps.txt',
            array("type","title","contains","limitation")
        );

        $this->dataList->loadAll();

        global $_GET;
        global $_POST;
        
        if( empty($_GET['subpage']) or ('sitemaps' != $_GET['subpage']) ) return;

        if( isSet($_GET['remove']) ){
            $this->dataList->remove($_GET['remove']);
            $this->refreshAll();
            $this->deleteObsolete();
        }

        if( isSet($_POST['itemsToRemove']) ){
            if( (isSet($_POST['action']) and ($_POST['action'] == 'remove') ) or (isSet($_POST['action2']) and ( $_POST['action2'] == 'remove') ) ){
                $this->dataList->removeMore( $_POST['itemsToRemove'] );
                $this->refreshAll();
                $this->deleteObsolete();
            }
        }

        if( isSet($_POST['sitemap_title'])
        and isSet($_POST['type'])
        and isSet($_POST['contains']) ){
            $this->addOrReplace($_POST);
            $this->refreshAll();
            $this->deleteObsolete();
        }

        $this->typeTitles = array( 'normal' => 'Normal', 'image' => 'Image', 'text' => 'Text' );

        $this->containsTypes = array();
        $this->containsTypes['tax_all'] = 'All taxonomies';
        $tax = get_taxonomies( array( 'public' => true ) ,'objects', 'and' );
        foreach ($tax as $key=>$definition ) {
            $this->containsTypes['tax_'.$key] = $definition->labels->name;
        }

        $postypes = get_post_types( array( 'public' => true ), 'objects', 'and' );
        $this->containsTypes['post_all'] = 'All post types';
        foreach ($postypes as $key=>$definition ) {
            $this->containsTypes['post_'.$key] = $definition->labels->name;
        }

        global $wp_taxonomies;
        // normal / public is: category | post_tag | post_format
        // hidden are: nav_menu | link_category

        $this->postTypesTaxonomies = array();
        $pTypes = get_post_types( array( 'public' => true ), 'object', 'and' );

        foreach ($pTypes as $pName => $pSettings ) {

            $this->postTypesTaxonomies[ 'post_'.$pName ] = array();

            foreach ($wp_taxonomies as $taxName => $taxSettings ) {
                if( $taxSettings->public and in_array( $pName, $taxSettings->object_type ) ) {
                    $this->postTypesTaxonomies[ 'post_'.$pName ][ $taxName ] = (object) array (
                        'label' => $taxSettings->label,
                        'hierarchical' => 1*$taxSettings->hierarchical,
                        'data' => (
                                    $taxSettings->hierarchical ?
                                    $this->getHiearchicalTerms( $taxName ) :
                                    $this->getNonHiearchicalTerms( $taxName )
                                  ),
                    );
                    
                }
            }
            
            if( empty( $this->postTypesTaxonomies[ 'post_'.$pName ] ) ){
                $this->postTypesTaxonomies[ 'post_'.$pName ] = null;
            }

        }

    }
    
    function getHiearchicalTerms( $tax_name, $parent = 0, $padding = 0 ){
        $ret = array();
        $terms = get_terms( $tax_name,  array('public' => true, 'parent' => $parent, 'hide_empty' => false ) );
        if  ($terms) {
            foreach ($terms as $term ) {
                $ret[ $term->term_id ] = (object) array(
                    'title' => $term->name . " (" . $term->count . ")",
                    'deep' => $padding,
                );
                $sub = $this->getHiearchicalTerms( $tax_name, $term->term_id, $padding+1 );
                foreach ($sub as $key=>$value) {
                    $ret[ $key ] = $value;
                }
                // array_merge will sort it :(
            }
        }
        return $ret;
    }

    function getNonHiearchicalTerms( $tax_name ){
        $ret = array();
        $terms = get_terms( $tax_name,  array('public' => true, 'hide_empty' => false ) );
        if  ($terms) {
            foreach ($terms as $term ) {
                $ret[ $term->term_id ] = (object) array(
                    'title' => $term->name . " (" . $term->count . ")",
                    'deep' => 0,
                );
            }
        }
        return $ret;
    }
    
    function printAdminPage(){
        global $_GET;

        $this->dataList->printErrors();
        $this->dataList->printInfos();

        if( isSet($_GET) and isSet($_GET['key']) ){
            $this->printEditForm( $_GET['key'] );
        }else{
            echo "<h2>";
            echo "<a href='".$this->dataList->PageSubpageFilterUrl."&key=' class='add-new-h2'>";
            echo "Add New Sitemap";
            echo "</a>";
            echo "</h2>";
            echo "<form action='".$this->dataList->PageSubpageFilterUrl."' method='post'>";

            $this->dataList->printFilter( $this->typeTitles );
            $this->dataList->printBulkActions('action');

            echo "<table class='wp-list-table widefat'>";

            $this->dataList->printHeadOrFooterHTML('thead', array( "Title","Type","Contains","Limitation By Taxonomy" ));
            $this->printList();
            $this->dataList->printHeadOrFooterHTML('tfoot', array( "Title","Type","Contains","Limitation By Taxonomy" ));

            echo "</table>";

            $this->dataList->printBulkActions('action2');
            echo "</form>";
        }
    }

    public function addOrReplace($d_POST){
        $this->dataList->loadAll();

        $fp = $this->dataList->prepareDataFileForWriting();

        if( empty($fp) ) return;

        $d_POST['limitation'] = ( empty($d_POST['limitation']) or ( FALSE !== strpos( '|'.$d_POST['limitation'].'|', '|0|' ) ) ) ?
                                '0' :
                                implode('|', $d_POST['limitation']);

        if( !empty( $_GET['keyToUpdate'] ) and !empty( $this->dataList->data[ $_GET['keyToUpdate'] ] ) ){
            unset( $this->dataList->data[ $_GET['keyToUpdate'] ] );
        }

        $this->dataList->data[ md5( $d_POST['sitemap_title'] ) ] = (object) array(
                  'type' => $d_POST['type'],
                  'title' => $d_POST['sitemap_title'],
                  'contains' => $d_POST['contains'],
                  'limitation' => $d_POST['limitation']
        );

        $this->dataList->saveAll($fp);
    }

    function printEditForm($key){
        $title = urldecode( $title );

        if( !empty($key) and isSet( $this->dataList->data[ $key ] ) ){
            $data = $this->dataList->data[ $key ];
        }else{
            $data = (object) array(
                "type" => "normal",
                "title" => "",
                "contains" => "tax_all",
                "limitation" => array( 0 => 0 ),
            );
        }

        $form_submit_path = $this->dataList->PageSubpageFilterUrl;
        require 'forms/sitemapsEditForm.php';
    }

    function printList(){
        if( empty($this->dataList->data) ){
            echo "<tr><td colspan=6><p>No Sitemaps</p></td></tr>";
            return;
        }

        global $_GET;
        $filter = ( isSet( $_GET['filter'] ) ) ? $_GET['filter'] : 'FALSE' ;
        if( $filter == 'FALSE' ){ $filter = ''; }

        foreach ($this->dataList->data as $md5uri=>$value) {

            if( '' != $filter ){
                if( $value->type != $filter ){
                    continue;
                }
            }

            echo "<tr>";
            echo "<th class='check-column'><input type='checkbox' name='itemsToRemove[]' value='$md5uri'></th>";
            echo "<td>";
            echo "<strong><a href='".$this->dataList->PageSubpageFilterUrl."&amp;key=$md5uri' title='Edit ".$value->title."' target=_blank>";
            echo $value->title.( ('text' == $value->type) ? ".txt" : ".xml" );
            echo "</a></strong>";
            echo "<div class='row-actions'>";
            echo "<span><a href='".$this->dataList->PageSubpageFilterUrl."&amp;key=$md5uri'>Edit</a></span> | ";
            echo "<span class='trash'><a href='".$this->dataList->PageSubpageFilterUrl."&amp;remove=$md5uri' class='submitdelete'>Remove</a></span> | ";
            echo "<span><a href='".site_url().'/sitemaps/'.($value->title.( ('text' == $value->type) ? ".txt" : ".xml" ))."' target=_blank>View</a></span>";
            echo "</div></td>";
            echo '</td>';
            echo '<td>'.$this->typeTitles[$value->type].'</td>';
            echo "<td>".$this->containsTypes[$value->contains]."</td>";
            echo "<td>";
            if( empty($this->postTypesTaxonomies[ $value->contains ]) or empty($value->limitation) ){
                echo "<strong>No limitation</strong>";
            }else{
                foreach ($this->postTypesTaxonomies[ $value->contains ] as $t=>$tData) {
                    foreach ($tData->data as $index=>$singleTData) {
                        if( FALSE !== strpos( '|'.$value->limitation.'|', '|'.$index.'|' ) ){
                            echo "<div>$tData->label: <strong> $singleTData->title </strong></div>";
                        }
                    }
                }
            }
            echo "</td>";
            echo '</tr>';
        }
    }
}

?>