<?php
define('MAX_404_ERROR_URL_TITLE_STRLEN', 256);

require_once 'sp404s.php';
require_once 'spListsAdmin.php';

class sp404sAdmin extends sp404s{

    public $googleIPs;
    public $facebookIPs;
    public $filterCounts;
    
    public $uri; // for 404 Not Found case

    function __construct( ){
        global $_GET;
        global $_POST;

        $this->dataList = new spListsAdmin(
            dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'others'.DIRECTORY_SEPARATOR.'404.txt',
            array('time','url404','urlReferer','eventHappened','ip')
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

        $this->googleIPs = file(SP_PLUGIN_ROOT.'others/ip_google.txt');
        foreach ($this->googleIPs as $key=>$value) $this->googleIPs[$key] = trim($value);

        $this->facebookIPs = file(SP_PLUGIN_ROOT.'others/ip_facebook.txt');
        foreach ($this->facebookIPs as $key=>$value) $this->facebookIPs[$key] = trim($value);
    }

    function printAdminPage(){
        global $_GET;

        $this->dataList->printErrors();
        $this->dataList->printInfos();

        ?>
        <style type="text/css">
        .wp-list-table td{min-width:24px}
        .wp-list-table .url404,
        .wp-list-table .referer404 {word-wrap: break-word;word-break: break-all;display:block;min-width:200px}
        .wp-list-table .datetime{ white-space: pre; }
        </style>
        <?php

        echo "<form action='".$this->dataList->PageSubpageFilterUrl."' method='post'>";

        $this->prepareList();

        $this->dataList->printFilter(
            array(
                'google' =>   'Googlebot ('.$this->filterCounts->google.')',
                'facebook' => 'Facebookbot ('.$this->filterCounts->facebook.')',
                'hacker' =>   'Hacker ('.$this->filterCounts->hacker.')',
                'unknown' =>  'Unknown referer ('.$this->filterCounts->unknown.')',
                'known' =>    'Known referer ('.$this->filterCounts->known.')',
            )
        );
        $this->dataList->printBulkActions('action');

        echo "<table class='wp-list-table widefat'>";

        $this->dataList->printHeadOrFooterHTML('thead', array( "Not Found URL", "Time", "Referer", "Last IP Address", "Happened" ));
        $this->printList();
        $this->dataList->printHeadOrFooterHTML('tfoot', array( "Not Found URL", "Time", "Referer", "Last IP Address", "Happened" ));

        echo "</table>";

        $this->dataList->printBulkActions('action2');
        echo "</form>";
    }

    function isPossibleHacker( $url ){
        $url = strtolower($url);

        $hacker_strings = array( '/pma/', '/phpmyadmin', '/myadmin', '/php-my-admin',
            '/phpadmin', '/php-admin', '/admin/', '/mysql', 'setup.php', 'timthumb.php',
            '?src=http',
        );

        foreach ($hacker_strings as $key=>$substring) {
            if( FALSE !== strpos( $url, $substring ) ){
                return TRUE;
            }
        }
        if( strlen($url) > 500 ){
          return TRUE;
        }
        return FALSE;
    }
    
    function prepareList(){
        $this->filterCounts = (object) array(
            'google' => 0,
            'facebook' => 0,
            'hacker' => 0,
            'unknown' => 0,
            'known' => 0,
            'total' => 0,
        );

        if( !empty( $this->dataList->data ) ){
            foreach ($this->dataList->data as $md5uri=>$value) {

                $this->filterCounts->total ++;

                $tmp = explode('.',$value->ip);
                $tmp = $tmp[0].'.'.$tmp[1].'.'.$tmp[2].'.';

                $this->dataList->data[$md5uri]->referer = '';
                if( in_array($tmp, $this->googleIPs) ){

                    $this->dataList->data[$md5uri]->referer = 'google';
                    $this->filterCounts->google ++;

                }else if( in_array($tmp, $this->facebookIPs) ){

                    $this->dataList->data[$md5uri]->referer = 'facebook';
                    $this->filterCounts->facebook ++;

                }else if( $this->isPossibleHacker( $value->url404 ) ){

                    $this->dataList->data[$md5uri]->referer = 'hacker';
                    $this->filterCounts->hacker ++;

                }else if( $value->urlReferer == 'Unknown referer' ){

                    $this->dataList->data[$md5uri]->referer = 'unknown';
                    $this->filterCounts->unknown ++;

                }else{

                    $this->dataList->data[$md5uri]->referer = 'known';
                    $this->filterCounts->known ++;

                }
            }
        }
    }

    function printDashboardList(){
        $this->prepareList();
        if( 0 == $this->filterCounts->total ){
            echo '<p><span class=ok>No 404 Page Not found</span></p>';
            return;
        }

        if( empty( $this->filterCounts->google ) ){
            echo '<p><span class=ok>Google did found all pages</span></p>';
        }else{
            echo '<p><span class=bad>There are '.$this->filterCounts->google.' page';
            if( 1 < $this->filterCounts->google ) echo 's';
            echo ' that Google was unable to find.</span></p>';
        }

        echo "<table class='wp-list-table widefat'>";
        echo '<thead><tr><th>404 Case</th><th>Count</th></tr></thead>';
        foreach($this->filterCounts as $key=>$value) {
            if( 'total' == $key ) continue;
            echo "<tr>";
            switch($key){
                case 'google':    echo  empty($value) ?
                                        "<td>Googlebot</td>" :
                                        "<td><a href='admin.php?page=sp_seo&subpage=404&filter=$key'>Googlebot</a></td>";
                                  break;
                case 'facebook':  echo  empty($value) ?
                                        "<td>Facebookbot</td>" :
                                        "<td><a href='admin.php?page=sp_seo&subpage=404&filter=$key'>Facebookbot</a></td>";
                                  break;
                case 'hacker':    echo  empty($value) ?
                                        "<td>Possible hacker</td>" :
                                        "<td><a href='admin.php?page=sp_seo&subpage=404&filter=$key'>Possible hacker</a></td>";
                                  break;
                case 'unknown':   echo  empty($value) ?
                                        "<td>Unknown Referer</td>" :
                                        "<td><a href='admin.php?page=sp_seo&subpage=404&filter=$key'>Unknown Referer</a></td>";
                                  break;
                default:          echo  empty($value) ?
                                        "<td>Known Referer</td>" :
                                        "<td><a href='admin.php?page=sp_seo&subpage=404&filter=$key'>Known Referer</a></td>";
                                  break;
            }
            echo "<td>".$value."</td>";
            echo "</tr>";
        }
        echo "<thead><tr><th><a href='admin.php?page=sp_seo&subpage=404'>Total Count</a></th><th>".$this->filterCounts->total."</th></tr></thead>";
        echo '</table>';
    }

    function printList(){
        if( empty($this->dataList->data) ){
            echo "<tr><td colspan=6><p>No 404</p></td></tr>";
            return;
        }
        
        global $_GET;
        $filter = ( isSet( $_GET['filter'] ) ) ? $_GET['filter'] : 'FALSE' ;
        if( $filter == 'FALSE' ){ $filter = ''; }

        $redir_subpath = str_replace($_GET['subpage'], 'redir', $this->dataList->PageSubpageUrl );
        
        foreach ($this->dataList->data as $md5uri=>$value) {
            $referer = $this->dataList->data[$md5uri]->referer;
            if( '' != $filter ){
                if( $referer != $filter ){
                    continue;
                }
            }

            echo "<tr>";
            echo "<th class='check-column'><input type='checkbox' name='itemsToRemove[]' value='$md5uri'></th>";
            echo "<td><strong><a href='".htmlentities($value->url404)."' class='url404'>".htmlentities(substr($value->url404,0,MAX_404_ERROR_URL_TITLE_STRLEN));
            if( MAX_404_ERROR_URL_TITLE_STRLEN <= strlen($value->url404) ) echo ' ..........';
            echo "</strong></a>";
            echo '<div class="row-actions">';
            echo "<span><a href=$redir_subpath&url=".urlencode($value->url404)." >Redirect</a></span> | ";
            echo "<span class='trash'><a href='".$this->dataList->PageSubpageFilterUrl."&remove=$md5uri' class='submitdelete'>Remove</a></span>";
            echo '</div>';
            echo "</td>";
            echo "<td class=datetime>".str_replace(' ','<br />',$value->time)."</td>";
            echo "<td>";
            switch($referer){
                case 'google':   echo "Googlebot"; break;
                case 'facebook': echo "Facebookbot"; break;
                case 'hacker':   echo "Possible hacker"; break;
                case 'unknown':  echo "N / A"; break;
                default:         echo "<a href='".$value->urlReferer."' class='referer404'>".$value->urlReferer."</a>"; break;
            }
            echo "</td>";
            echo "<td>".$value->ip."</td>";
            echo "<td>".$value->eventHappened." &times;</td>";
            echo '</tr>';
        }
    }

}
  
  
?>