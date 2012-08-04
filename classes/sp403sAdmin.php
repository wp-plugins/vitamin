<?php
define('MAX_403_ERROR_URL_TITLE_STRLEN', 256);

require_once 'sp403s.php';
require_once 'spListsAdmin.php';

class sp403sAdmin extends sp403s{

    public $googleIPs;
    public $facebookIPs;
    public $filterCounts;
    
    public $uri; // for 403 Forbidden case

    function __construct( ){
        global $_GET;
        global $_POST;

        $this->dataList = new spListsAdmin(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'403.txt',
            array('time','url403','eventHappened','ip')
        );

        $this->dataList->loadAll();

        if( isSet($_GET['remove']) ){
            $this->dataList->remove($_GET['remove']);
        }

        if( isSet($_POST['itemsToRemove']) ){
            if( (isSet($_POST['action']) and ($_POST['action'] == 'remove') ) or (isSet($_POST['action2']) and ( $_POST['action2'] == 'remove') ) ){
                $this->dataList->removeMore( $_POST['itemsToRemove'] );
            }
        }
    }

    function printAdminPage(){
        global $_GET;

        $this->dataList->printErrors();
        $this->dataList->printInfos();

        ?>
        <style type="text/css">
        .wp-list-table td{min-width:24px}
        .wp-list-table .url403 {word-wrap: break-word;word-break: break-all;display:block;min-width:200px}
        .wp-list-table .datetime{ white-space: pre; }
        </style>
        <?php

        echo "<form action='".$this->dataList->PageSubpageFilterUrl."' method='post'>";

        $this->dataList->printBulkActions('action');

        echo "<table class='wp-list-table widefat'>";

        $this->dataList->printHeadOrFooterHTML('thead', array( "Forbidden URL", "Time", "Last IP Address", "Happened" ));
        $this->printList();
        $this->dataList->printHeadOrFooterHTML('tfoot', array( "Forbidden URL", "Time", "Last IP Address", "Happened" ));

        echo "</table>";

        $this->dataList->printBulkActions('action2');
        echo "</form>";
    }

    function printList(){
        if( empty($this->dataList->data) ){
            echo "<tr><td colspan=6><p>No 403 / No recorded hacker attempts</p></td></tr>";
            return;
        }
        
        global $_GET;
        $filter = ( isSet( $_GET['filter'] ) ) ? $_GET['filter'] : 'FALSE' ;
        if( $filter == 'FALSE' ){ $filter = ''; }

        $redir_subpath = str_replace($_GET['subpage'], 'redir', $this->dataList->PageSubpageUrl );
        
        foreach ($this->dataList->data as $md5uri=>$value) {
            echo "<tr>";
            echo "<th class='check-column'><input type='checkbox' name='itemsToRemove[]' value='$md5uri'></th>";
            echo "<td><strong><a href='".htmlentities($value->url403)."' class='url403'>".htmlentities(substr($value->url403,0,MAX_403_ERROR_URL_TITLE_STRLEN));
            if( MAX_403_ERROR_URL_TITLE_STRLEN <= strlen($value->url403) ) echo ' ..........';
            echo "</strong></a>";
            echo '<div class="row-actions">';
            echo "<span class='trash'><a href='".$this->dataList->PageSubpageFilterUrl."&remove=$md5uri' class='submitdelete'>Remove</a></span>";
            echo '</div>';
            echo "</td>";
            echo "<td class=datetime>".str_replace(' ','<br />',$value->time)."</td>";
            echo "<td>".$value->ip."</td>";
            echo "<td>".$value->eventHappened." &times;</td>";
            echo '</tr>';
        }
    }

}
  
  
?>