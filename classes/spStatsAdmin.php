<?php

require_once 'spStats.php';

class spStatsAdmin extends spStats {

    function printStatsCacheHitsAndMiss(){
        $stats_titles = array(
            'hit' => 'Cache hits',
            '304' => 'Cache hits, 304 Not Modified Header',
            'miss' => 'Cache miss',
            'htaccess_miss' => 'Htaccess mode Cache miss',
            'htaccess_hit' => 'Htaccess mode Cache hit - disabled mod_headers',
        );
        
        $stats_sum = 0;
        foreach ($stats_titles as $key=>$title) {
            $stats_sum += $this->statsData[$key];
        }
        $stats_sum = ( $stats_sum > 0 ) ? $stats_sum : 1;
        ?>
                <h4 style="padding:7px 0">Details for caching</h4>

                <table class='widefat'>
                  <thead>
                    <tr>
                      <th>Stat Case</th>
                      <th>Count</th>
                      <th>Percentage</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats_titles as $key=>$title) { ?>
                    <tr>
                      <td><?php echo $title; ?></td>
                      <td><?php echo $this->statsData[$key]; ?></td>
                      <td><?php echo number_format( 100 * $this->statsData[$key] / $stats_sum, 1); ?>%</td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
        <?php
    }
    
    function showCacheGraph( ){
        $hits = $this->statsData['hit'] + $this->statsData['304'];
        $miss = $this->statsData['miss'];

        $ht_hits = $this->statsData['htaccess_hit'];
        $ht_miss = $this->statsData['htaccess_miss'];

        if( !empty( $ht_hits ) ){
            // Eh, htaccess does not know mod_headers.c :(
            $hits += $ht_hits; $ht_hits = 0;
            $miss += $ht_miss; $ht_miss = 0;
        }

        if( 0 == $hits + $miss ){
            echo "<p><span class=bad>Not enough data: Cache hits + Cache miss is zero </span></p>";
            return;
        }else{
            echo '<div id=chart_cache_hit_vs_miss style="float:left;margin:0 15px 10px 0;border:1px solid #DFDFDF;background:#FFF;border-radius:3px;"></div>';
        }

        if( ! empty( $ht_miss ) ){
            if( ! empty( $ht_miss ) and ! empty( $miss ) ){
                $hits = ceil( $hits * ( 1 + ( $ht_miss/$miss ) ) );
                $miss = $miss + $ht_miss;
            }
            echo "<p class=ok>Data count on graph was aproximated by PHP Cache mode stats.</p>";
        }

        echo "<p>Server cache is a mechanism for the temporary storage (caching) of HTML pages, to reduce server load and reduce loading lag.</p>";
        echo "<p>When somebody opens your page, that page is sent to him, but there is also created a copy of it. This is called a cache miss.</p>";
        echo "<p>When second visitor opens the same page, the saved copy is sent to him without slow rebuilding. This is called a cache hit.</p>";
        ?>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript">
          google.load('visualization', '1.0', {'packages':['corechart']});
          google.setOnLoadCallback(drawChart);
          function drawChart() {
          var data = new google.visualization.DataTable();
          data.addColumn('string', 'State');
          data.addColumn('number', 'Count');
          data.addRows([
            ['Cache hit', <?php echo $hits; ?> ],
            ['Cache miss', <?php echo $miss; ?> ]
          ]);
          var options = {
                          width:290,
                          height:210,
                          chartArea:{left:30,top:10,width:"100%",height:"90%"}
                        };
          var chart = new google.visualization.PieChart(document.getElementById('chart_cache_hit_vs_miss'));
          chart.draw(data, options);
        }
        </script>
        <?php
    }

    function printAdminPage(){
        $board = spClasses::get('Board');

        $board->beforeForm();

        $board->beforeBox('Numbers');

        $stats_sum = array_sum($this->statsData);
        $stats_sum = ( $stats_sum > 0 ) ? $stats_sum : 1;

        $stats_titles = array(
            'hit' => 'Cache hits',
            '304' => 'Cache hits, 304 Not Modified Header',
            'miss' => 'Cache miss',
            'htaccess_miss' => 'Htaccess mode Cache miss',
            'htaccess_hit' => 'Htaccess mode Cache hit - disabled mod_headers',
            'disabled' => '<a href="./admin.php?page=sp_speed&subpage=html_cache" target=_blank>Cache disabled by substring</a>',
            'admin' => 'Cache disabled - admin view',
            '404' => '<a href="./admin.php?page=sp_seo&subpage=404" target=_blank>404 Page Not Found</a>',
            '301' => '<a href="./admin.php?page=sp_seo&subpage=redir" target=_blank>301 Moved Permanently</a>',
            '302' => '<a href="./admin.php?page=sp_seo&subpage=redir" target=_blank>302 Found (originally temporary redirect)</a>',
            '307' => '<a href="./admin.php?page=sp_seo&subpage=redir" target=_blank>307 Temporary Redirect</a>',
            '403' => '<a href="./admin.php?page=sp_security&subpage=blocks" target=_blank>403 Forbidden / Hacker attack blocks</a>',
        );
        ?>
                <table class='widefat'>
                  <thead>
                    <tr>
                      <th>Stat Case</th>
                      <th>Count</th>
                      <th>Percentage</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats_titles as $key=>$title) { ?>
                    <tr>
                      <td><?php echo $title; ?></td>
                      <td><?php echo $this->statsData[$key]; ?></td>
                      <td><?php echo number_format( 100 * $this->statsData[$key] / $stats_sum, 1); ?>%</td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
        <?php
        $board->showButton('clear_cache_stats=true', 'Clear stats');
        $board->afterBox();

        $board->columnSeparator();
        
        $board->beforeBox('Graph');
        if( 0 == array_sum($this->statsData) ){
            echo '<p>All is empty.</p>';
        }else{
            echo '<div id=chart_stats></div>';
            ?>
            <script type="text/javascript" src="https://www.google.com/jsapi"></script>
            <script type="text/javascript">
              google.load('visualization', '1.0', {'packages':['corechart']});
              google.setOnLoadCallback(drawChart);
              function drawChart() {
              var data = new google.visualization.DataTable();
              data.addColumn('string', 'State');
              data.addColumn('number', 'Count');
              data.addRows([
                  ['Cache hits', <?php echo $this->statsData['hit']; ?> ],
                  ['Cache hits, 304 Not Modified Header', <?php echo $this->statsData['304']; ?> ],
                  ['Cache miss', <?php echo $this->statsData['miss']; ?> ],
                  ['Htaccess mode Cache miss', <?php echo $this->statsData['htaccess_miss']; ?> ],
                  ['Htaccess mode Cache hit - disabled mod_headers', <?php echo $this->statsData['htaccess_hit']; ?> ],

                  ['Cache disabled by substring', <?php echo $this->statsData['disabled']; ?> ],
                  ['Cache disabled - admin view', <?php echo $this->statsData['admin']; ?> ],

                  ['404 Page Not Found', <?php echo $this->statsData['404']; ?> ],

                  ['301 Moved Permanently', <?php echo $this->statsData['301']; ?> ],
                  ['302 Found', <?php echo $this->statsData['302']; ?> ],
                  ['307 Temporary Redirect', <?php echo $this->statsData['307']; ?> ],
                  ['403 Forbidden / Hacker attack blocks', <?php echo $this->statsData['403']; ?> ]
              ]);
              var options = {
                              width:"100%",
                              height:350,
                              fontSize:12,
                              backgroundColor:"transparent",
                              chartArea:{left:10,top:10,width:"95%",height:"90%"}
                            };
              var chart = new google.visualization.PieChart(document.getElementById('chart_stats'));
              chart.draw(data, options);
            }
            </script>
            <?php
        }
        $board->afterBox();
        $board->afterForm();
    }
}
  
  
?>