<?php

require_once 'spRedirs.php';
require_once 'spListsAdmin.php';

class spRedirsAdmin extends spRedirs {

    function __construct( ){
        global $_GET;
        global $_POST;

        $this->dataList = new spListsAdmin(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'redir.txt',
            array('priority','orgUrl','code','redirUrl')
        );

        $this->loadAll();

        if( empty($_GET['subpage']) or ('redir' != $_GET['subpage']) ) return;

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
        
        if( isSet($_POST['orgUrl'])
        and isSet($_POST['redirUrl'])
        and isSet($_POST['code'])
        and isSet($_POST['priority'])
        and isSet($_POST['add_redir']) ){
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
            echo "Add New Redirection";
            echo "</a>";
            echo "</h2>";
            echo "<form action='".$this->dataList->PageSubpageFilterUrl."' method='post'>";

            $this->dataList->printFilter( array(
                          "301" => "301 Moved Permanently",
                          "302" => "302 Found",
                          "307" => "307 Temporary Redirect",
            ) );
            $this->dataList->printBulkActions('action');

            echo "<table class='wp-list-table widefat'>";

            $this->dataList->printHeadOrFooterHTML('thead', array( "Redirected","Priority","Code","Redirect to" ));
            $this->printList();
            $this->dataList->printHeadOrFooterHTML('tfoot', array( "Redirected","Priority","Code","Redirect to" ));

            echo "</table>";

            $this->dataList->printBulkActions('action2');
            echo "</form>";
        }
    }

    function printEditForm($key, $url=null ){
        if( isSet( $this->dataList->data[ $key ] ) ){
            $data = $this->dataList->data[ $key ];
        }else{
            $data = (object) array(
                'priority' => 5,
                'orgUrl' => $url,
                'code' => 301,
                'redirUrl' => '/',
            );
        }
        $form_submit_path = $this->dataList->PageSubpageFilterUrl;
        require 'forms/redirsEditForm.php';
    }

    function printList(){
        if( empty($this->dataList->data) ){
            echo "<tr><td colspan=6><p>No redirects</p></td></tr>";
            return;
        }

        global $_GET;
        $filter = ( isSet( $_GET['filter'] ) ) ? $_GET['filter'] : 'FALSE' ;
        if( $filter == 'FALSE' ){ $filter = ''; }

        $page = empty($_GET['page'])?'':$_GET['page'];
        $subpage = empty($_GET['subpage'])?'':$_GET['subpage'];
        
        foreach ($this->dataList->data as $md5uri=>$value) {
            if( '' != $filter ){
                if( $value->code != $filter ){
                    continue;
                }
            }

            $redirUrl_Nice = $value->redirUrl;
            $redirUrl_Nice = htmlentities($redirUrl_Nice);
            $redirUrl_Nice = str_replace('/','<strong style="color:#000;padding:0 2px">/</strong>',$redirUrl_Nice);
            $redirUrl_Nice = str_replace('%anything%','<span style="color:#AAA">%anything%</span>',$redirUrl_Nice);

            $orgUrl_Nice = $value->orgUrl;
            $orgUrl_Nice = htmlentities($orgUrl_Nice);
            $orgUrl_Nice = str_replace('/','<strong style="color:#000;padding:0 2px">/</strong>',$orgUrl_Nice);
            $orgUrl_Nice = str_replace('%anything%','<span style="color:#AAA">%anything%</span>',$orgUrl_Nice);

            echo "<tr>";
            echo "<th class='check-column'><input type='checkbox' name='itemsToRemove[]' value='$md5uri'></th>";
            echo "<td>".$orgUrl_Nice;
            echo "<div class='row-actions'>";
            echo "<span><a href='".$this->dataList->PageSubpageFilterUrl."&amp;key=$md5uri'>Edit</a></span> | ";
            echo "<span class='trash'><a href='admin.php?page=$page&subpage=$subpage&remove=$md5uri' class='submitdelete'>Remove</a></span>";
            echo "</div></td>";
            echo "<td>".$value->priority."</td>";
            echo "<td>".$value->code."</td>";
            echo "<td>$redirUrl_Nice</td>";
            echo '</tr>';
        }
    }

}
  
  
?>