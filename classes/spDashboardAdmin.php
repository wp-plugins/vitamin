<?php

require_once 'spBoard.php';

class spDashboardAdmin extends spBoard {

    function printAdminPage(){

        global $_GET;

        $hooks = spClasses::get('Hooks');
        if( isSet($_GET['delete_cached_pages']) ){  $hooks->add('deleteCache'); }
        $hooks->run();
        
        $settings = spClasses::get('Settings');
        $stats = spClasses::get('Stats');
        $blocks = spClasses::get('Blocks');

        $this->printOkAndbadCss();
        
        $this->beforeForm();

        /*
        $this->beforeBox('Donate');
        $this->donate();
        $this->afterBox();
        */

        $this->beforeBox('404 Page Not Found');
        $pages404 = spClasses::get('404s');
        $pages404->printDashboardList();
        $this->afterBox();

        $this->beforeBox('Hacker RFI Attacks');
        echo "<p><span class=ok>Hacker RFI Attacks blocked: ".$stats->statsData[403].".</span></p>";
        /*
        if( ! empty( $blocks->dataList->data ) ){
            echo "<pre>";
            foreach ($blocks->dataList->data as $md5uri=>$value) {
                $orgUrl_Nice = $value->orgUrl;
                $orgUrl_Nice = htmlentities($orgUrl_Nice);
                $orgUrl_Nice = str_replace('/','<strong style="color:#000;padding:0 2px">/</strong>',$orgUrl_Nice);
                $orgUrl_Nice = str_replace('%anything%','<span style="color:#AAA">%anything%</span>',$orgUrl_Nice);
                echo $orgUrl_Nice."\n";
            }
            echo "</pre>";
        }
        */
        echo '<p style="text-align:right">';
        echo '<a href="./admin.php?page=sp_security&amp;subpage=blocked" class="button-primary">View blocked attempts</a>';
        echo ' &nbsp; ';
        echo '<a href="./admin.php?page=sp_security&amp;subpage=blocks" class="button-primary">Edit strings to block</a>';
        echo '</p>';


        $this->afterBox();

        $this->columnSeparator();

        $this->beforeBox('Cache Mode');

        switch ($settings->cache_level) {
            case 0:
                echo '<p>';
                echo '<span class=bad>Cache is OFF</span>';
                echo 'Without caching is whole plugin useless.';
                echo '</p>';
                break;
            case 1:
                echo '<p>';
                echo '<span class=ok>Cache mode by PHP</span>';
                echo 'Great speed boost for your site.';
                echo '</p>';
                break;
            case 2:
                if( empty($stats->statsData['htaccess_hit']) ){
                    echo '<p>';
                    echo '<span class=ok>Cache mode by htaccess</span>';
                    echo 'Excelent speed boost for your site. ';
                    echo 'When cache is used, just pure HTML and nothing more is used. ';
                    echo 'But in this case is disabled some statistics.';
                    echo '</p>';
                }else{
                    echo '<p>';
                    echo '<span class=bad>Cache mode by htaccess with problems :(</span>';
                    echo 'Cache mode may be disabled, when your hosting is unable to answer on requests for compressed files. ';
                    echo 'In this case is aplied PHP Cache.';
                    echo '</p>';
                }
                break;
            default:
                echo '<p>';
                echo '<span class=bad>Unknown cache mode</span>';
                echo 'Fatal error - plugin does not know what cache mode is on.';
                echo '</p>';
                break;
        }
        
        echo '<p>';
        echo 'You may change cache mode in <a href="./admin.php?page=sp_speed&subpage=html_cache">Vitamin &gt; Speed &gt; HTML Cache</a>.';
        echo '</p>';

        if( 1 == $settings->cache_level OR 2 == $settings->cache_level ){
                $this->showButton('delete_cached_pages=true', 'Delete Cached Pages');
        }

        $this->afterBox();

        $this->beforeBox('Cache Hits vs Miss');
        $stats->showCacheGraph();
        $stats->printStatsCacheHitsAndMiss();
        echo '<p style="text-align:right">';
        echo '<a href="./admin.php?page=sp_dashboard&subpage=stats" class="button-primary">View all stats</a>';
        echo '</p>';
        $this->afterBox();

        $this->afterForm();
    }
    
    function donate(){
    ?>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
      <p>
          <span class=bad>Do not forget to donate!</span>
      </p>
      <table>
        <tr>
          <td><label for="item_name">Message: </label></td>
          <td><input type="text" name="item_name" id="item_name" value="For Vitamin WordPress Plugin Development" size="60"></td>
        </tr>
        <tr>
          <td>Amount:</td>
          <td>
          <label><input type="radio" name="amount" id="amount" value=5 /> $5 </label> &nbsp; &nbsp;
          <label><input type="radio" name="amount" id="amount" value=10 checked="checked" /> $10 </label> &nbsp; &nbsp;
          <label><input type="radio" name="amount" id="amount" value=20 /> $20 </label> &nbsp; &nbsp;
          <label><input type="radio" name="amount" id="amount" value=40 /> $50 </label> &nbsp; &nbsp;

          <img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
          <input type="hidden" name="cmd" value="_donations">
          <input type="hidden" name="business" value="wp.vitamin.donations@gmail.com">
          <input type="hidden" name="currency_code" value="USD">
        </tr>
        <tr>
          <td> &nbsp; </td>
          <td>
            <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online.">
          </td>
      </table>
    </form>
    <?php
    }
}
