<?php

class spBoard{

    function beforeBox($title="undefined title"){
      ?>
              <div class="postbox">
                <h3 style="cursor:text"><?php echo $title; ?></h3>
                <div class="inside">
      <?php
    }

    function afterBox(){ echo '</div></div>'; }
    
    function showButton($param, $title, $where=null){
        global $_GET;
        echo '<p style="text-align:right"><a href="';
        if( empty($where) ){
            echo 'admin.php?page=';
            echo empty($_GET['page'])?'':$_GET['page'];
            echo '&amp;subpage=';
            echo empty($_GET['subpage'])?'':$_GET['subpage'];
            if( !empty($param) ){
                echo '&amp;';
            }
        }else{
            echo $where;
        }
        echo $param;
        echo '" class="button-primary">';
        echo $title;
        echo '</a></p>';
    }
    
    function printOkAndbadCss(){
        echo '<style type="text/css">';
        echo '.ok {font-weight:bold;color:#0C0;display:block}';
        echo '.info{font-weight:bold;display:block}';
        echo '.bad{font-weight:bold;color:#C00;display:block}';
        echo '</style>';
    }
    
    function beforeForm(){
        ?>
            <div id="dashboard-widgets-wrap">
              <div id="dashboard-widgets" class="metabox-holder">
                <div id="postbox-container-1" class="postbox-container" style="width:50%;">
                  <div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <?php
    }
    
    function columnSeparator(){
        ?>
                  </div>
                </div>
                <div id="postbox-container-2" class="postbox-container" style="width:50%;">
                  <div id="side-sortables" class="meta-box-sortables ui-sortable">
      <?php
    }
    
    function afterForm(){
        ?>
                  </div>
                </div>
              </div>
              <div class="clear"></div>
            </div>
        <?php
    }
}
