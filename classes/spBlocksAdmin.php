<?php

require_once 'spListsAdmin.php';

class spBlocksAdmin {

    function __construct( ){
        global $_GET;
        global $_POST;

        $this->dataList = new spListsAdmin(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'blocks.txt',
            array('orgUrl')
        );

        $this->dataList->loadAll();

        if( empty($_GET['subpage']) or ('blocks' != $_GET['subpage']) ) return;

        if( isSet($_GET['remove']) ){
            $this->dataList->remove($_GET['remove']);
            $this->refreshHtaccess();
        }

        if( isSet($_POST['itemsToRemove']) ){
            if( (isSet($_POST['action']) and ($_POST['action'] == 'remove') ) or (isSet($_POST['action2']) and ( $_POST['action2'] == 'remove') ) ){
                $this->dataList->removeMore( $_POST['itemsToRemove'] );
                $this->refreshHtaccess();
            }
        }
        
        if( isSet($_POST['orgUrl']) ){
            $this->addOrReplace($_POST);
            $this->refreshHtaccess();
        }
        
    }
    
    function refreshHtaccess(){
        $spHtaccess = spClasses::get('Htaccess');
        $spHtaccess->refreshHtaccess();
    }
    
    function printAdminPage(){
        global $_GET;
        
        $this->dataList->printErrors();
        $this->dataList->printInfos();

        if( isSet($_GET) and isSet($_GET['key']) ){
            $this->printEditForm( $_GET['key'] );
        }else if( isSet($_GET) and isSet($_GET['url']) ){
            $this->printEditForm( null, $_GET['url']);
        }else{
            echo "<h2>";
            echo "<a href='".$this->dataList->PageSubpageFilterUrl."&url=' class='add-new-h2'>";
            echo "Add New RFI Hacker Block";
            echo "</a>";
            echo "</h2>";
            echo "<form action='".$this->dataList->PageSubpageFilterUrl."' method='post'>";

            $this->dataList->printBulkActions('action');

            echo "<table class='wp-list-table widefat'>";

            $this->dataList->printHeadOrFooterHTML('thead', array( "Type", "Blocked Substring" ));
            $this->printList();
            $this->dataList->printHeadOrFooterHTML('tfoot', array( "Type", "Blocked Substring" ));

            echo "</table>";

            $this->dataList->printBulkActions('action2');
            echo "</form>";
        }
    }

    public function addOrReplace($d_POST){
        $fp = $this->dataList->prepareDataFileForWriting();
        if( empty($fp) ) return;

        $d_POST['orgUrl'] = stripslashes($d_POST['orgUrl']);
        
        if( '//' == substr($d_POST['orgUrl'], 0, 2) ) $d_POST['orgUrl'] = substr($d_POST['orgUrl'], 2);
        if( 'http://' == substr($d_POST['orgUrl'], 0, 7) ) $d_POST['orgUrl'] = substr($d_POST['orgUrl'], 7);
        if( 'https://'== substr($d_POST['orgUrl'], 0, 8) ) $d_POST['orgUrl'] = substr($d_POST['orgUrl'], 8);

        if( !empty($_SERVER['SERVER_NAME']) ){
            if( 0 === strpos($d_POST['orgUrl'],$_SERVER['SERVER_NAME']) ){
                $d_POST['orgUrl'] = substr($d_POST['orgUrl'], strlen($_SERVER['SERVER_NAME']) );
            }
        }

        global $_GET;
        if( !empty( $_GET['keyToUpdate'] ) and !empty( $this->dataList->data[ $_GET['keyToUpdate'] ] ) ){
            unset( $this->dataList->data[ $_GET['keyToUpdate'] ] );
        }

        $this->dataList->data[ md5( $d_POST['orgUrl'] ) ] = (object) array( 'orgUrl' => $d_POST['orgUrl'] );

        if( $this->dataList->saveAll($fp)){
            global $_GET;
            if( !empty( $_GET['keyToUpdate'] ) ){
                $this->dataList->info[] = "Item <code>".htmlentities($d_POST['orgUrl'])."</code> was updated";
            }else{
                $this->dataList->info[] = "Item <code>".htmlentities($d_POST['orgUrl'])."</code> was added";
            }
        }else{
            $this->dataList->error[] = "Unable to add <code>".htmlentities($d_POST['orgUrl'])."</code> item";
        }

    }

    function printEditForm($key, $url=null ){
        if( isSet( $this->dataList->data[ $key ] ) ){
            $data = $this->dataList->data[ $key ];
        }else{
            $data = (object) array(
                'orgUrl' => $url,
            );
        }
        $form_submit_path = $this->dataList->PageSubpageFilterUrl;

        global $_GET;
        $key = empty($_GET['key']) ? null : $_GET['key'];
        require 'forms/blocksEditForm.php';
        new blocksEditForm($data, $form_submit_path, $key);
    }

    function printList(){
        if( empty($this->dataList->data) ){
            echo "<tr><td colspan=6><p>No Blocks</p></td></tr>";
            return;
        }

        global $_GET;
        $filter = ( isSet( $_GET['filter'] ) ) ? $_GET['filter'] : 'FALSE' ;
        if( $filter == 'FALSE' ){ $filter = ''; }

        $page = empty($_GET['page'])?'':$_GET['page'];
        $subpage = empty($_GET['subpage'])?'':$_GET['subpage'];
        
        foreach ($this->dataList->data as $md5uri=>$value) {

            $orgUrl_Nice = $value->orgUrl;
            $orgUrl_Nice = htmlentities($orgUrl_Nice);
            $orgUrl_Nice = str_replace('/','<strong style="color:#000;padding:0 2px">/</strong>',$orgUrl_Nice);
            $orgUrl_Nice = str_replace('%anything%','<span style="color:#AAA">%anything%</span>',$orgUrl_Nice);

            echo "<tr>";
            echo "<th class='check-column'><input type='checkbox' name='itemsToRemove[]' value='$md5uri'></th>";
            echo "<td style='width:100px'>";
            echo ('?' == substr($value->orgUrl,0,1) ) ?
                 "In query" :
                 "In file name";
            echo "</td>";
            echo "<td>".$orgUrl_Nice;
            echo "<div class='row-actions'>";
            echo "<span><a href='".$this->dataList->PageSubpageFilterUrl."&amp;key=$md5uri'>Edit</a></span> | ";
            echo "<span class='trash'><a href='admin.php?page=$page&subpage=$subpage&remove=$md5uri' class='submitdelete'>Remove</a></span>";
            echo "</div></td>";
            echo '</tr>';
        }
    }

}
  
  
?>