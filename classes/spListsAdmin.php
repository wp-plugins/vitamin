<?php

function_exists ('is_admin') or exit;
is_admin()                   or exit;
defined('SP_PLUGIN_ROOT')    or exit;
defined('SP_ABSPATH')        or exit;

require_once 'spLists.php';

class spListsAdmin extends spLists {
    public $PageSubpageUrl;
    public $PageSubpageFilterUrl;

    function __construct($dataFile, $dataDefinition){
        $this->dataFile = $dataFile;
        $this->dataDefinition = $dataDefinition;
        $this->columnCount = count( $dataDefinition );

        global $_GET;

        $this->PageSubpageUrl = "admin.php?page=".
                                ( empty($_GET['page'])?'':$_GET['page'] ) .
                                "&subpage=".
                                ( empty($_GET['subpage'])?'':$_GET['subpage'] ) ;
        $this->PageSubpageFilterUrl = $this->PageSubpageUrl .
                                      "&filter=".
                                      ( empty($_GET['filter'])?'FALSE':$_GET['filter'] ) ;
    }

    function remove($MD5url){
        $this->loadAll();
        if( empty($this->data[$MD5url]) ){
            $this->error[] = 'Item to delete was not found';
            return FALSE;
        }
        $item = (array) $this->data[$MD5url];
        $item = $item[ $this->dataDefinition[1] ];
        unset( $this->data[$MD5url] );
        if ( $this->saveAll() ){
            $this->info[] = "Item <code>".htmlentities($item)."</code> was removed";
        }
    }

    function removeMore($urlsARR){
        $this->loadAll();
        foreach ($urlsARR as $index=>$MD5url) {
            unset( $this->data[$MD5url] );
        }
        if ( $this->saveAll() ){
            $this->info[] = "Selected items were removed";
        }
    }
    
    function printErrors(){
        if( !empty( $this->error ) ){
            foreach ($this->error as $index=>$value) {
                echo '<div class="error below-h2"><p><strong>'.$value.'</strong></p></div>';
            }
        }
    }
    
    function printInfos(){
        if( !empty( $this->info ) ){
            foreach ($this->info as $index=>$value) {
                echo '<div class="updated"><p>'.$value.'</p></div>';
            }
        }
    }

    function printFilter($possible_filters){
        global $_GET;
        $filter = ( isSet( $_GET['filter'] ) ) ? $_GET['filter'] : 'FALSE' ;
        if( $filter == 'FALSE' ){ $filter = ''; }

        echo "<ul class=subsubsub>";
        echo "<li><a href='".$this->PageSubpageUrl."'";
        if(''==$filter) echo " class=current";
        echo ">All</a></li>";
        foreach($possible_filters as $fltr=>$title) {
            echo "<li>| <a href='".$this->PageSubpageUrl."&filter=$fltr'";
            if( $fltr == $filter ) echo " class=current";
            echo ">".$title."</a> </li>";
        }
        echo "</ul>";
    }

    function printBulkActions($name){
    ?>
    <div class="tablenav">
      <div class="alignleft actions">
        <select name="<?php echo $name; ?>">
          <option value="0" selected="selected">Bulk Actions</option>
          <option value="remove">Remove</option>
        </select>
        <input type="submit" name="" id="doaction" class="button-secondary action" value="Apply">
      </div>
      <br class="clear">
    </div>
    <?php
    }
    
    function printHeadOrFooterHTML($what, $titles){
        echo "<$what>";
        echo "<tr>";
        echo "<th class='manage-column column-cb check-column'><input type='checkbox'></th>";
        foreach ($titles as $i=>$title) {
            echo "<th>$title</th>";
        }
        echo "</tr>";
        echo "</$what>";
    }

    
}