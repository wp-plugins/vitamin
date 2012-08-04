<?php
$spSettingsAdminInfo = array(

    // SEO > Main SEO Files

    'sp_file_edit_htaccess' => (object) array(
        'title' => 'File .htaccess',
        'description' => 'If you write your own code into .htaccess file, do not write your own code between <code># BEGIN SP</code> and <code># END SP</code>. '.
                         'If you enable again automatic updates by plugin, your code may be overwritten. If writing to htaccess is enabled, plugin code is automatically moved to top in .htaccess file.',
        'option' => array(0 => 'Leave it', 1=> 'Write it'),
    ),

    'sp_file_robots_txt' => (object) array(
        'title' => 'File robots.txt',
        'description' => 'Minimal recomended settings is:<br />'.
                         '<code>User-agent: *'.
                         '<br />Sitemap: '.home_url().'/sitemap.xml'.
                         '</code>',
        'option' => array(0 => 'Leave it', 1=> 'Write it'),
    ),

    'sp_file_root_sitemap_xml' => (object) array(
        'title' => 'Root index sitemap file sitemap.xml',
        'description' => 'You may insert in your root index sitemap even RSS and Atom publishing protocol.',
        'option' => array(0 => 'Leave it', 1=> 'Write it'),
    ),

    'sp_add_rss_to_robots_sitemap' => (object) array(
        'title' => 'Blog RSS Feed',
        'option'=>array(
            0 => 'Do not care.',
            1 => 'Add Blog RSS Feed',
        ),
    ),

    'sp_add_atom_to_robots_sitemap' => (object) array(
        'title' => 'Blog Atom Feed',
        'description' => 'Atom Publishing Protocol is ' .( get_option('enable_app') ? 'enabled' : '<strong>DISABLED</strong>' ). ' in settings.
                To enable that
                go to <a href="./options-writing.php">Settings
                &gt; Writing</a>, search for "Remote Publishing" &gt;
                "Atom Publishing Protocol" and enable option
                "Enable the Atom Publishing Protocol".',
        'option'=>array(
            0 => 'Do not care.',
            1 => 'Add Blog Atom Feed',
        ),
    ),

    // Speed > Server Cache

    'sp_cache_level' => (object) array(
        'title' => 'Cache level',
        'description' => 'Themes and plugins in WordPress often don’t cooperate, but if web visitor gets a page, then this page should be in 99,9% cases same as non-cached version. It is a main reason why you may choose these options.',
        'option'=>array(
              0=>'Off - disable cache completely',
              1=>'PHP cache - overall the best but not as fast as .htaccess Server cache',
              2=>'.htaccess Server cache - the fastest, but does not use dynamic functions e.g., cache statistics',
        ),
    ),
    'sp_cache_reaction_on_comments' => (object) array(
        'title' => 'Cache reaction on comments',
        'description' => 'The best solution for this is to hide comments count in categories and delete just one page or post, when comment is send.',
        'option'=>array(
              0=>'Delete whole cache',
              1=>'Delete just page,where comment is send',
        ),
    ),
    'sp_cache_reaction_on_session' => (object) array(
        'title' => 'Cache reaction on sessions',
        'description' => 'Please, if you do not know what is session, use "Disable" option.',
        'option'=>array(
              'disable_cache'=>'Disable cache if session is detected',
              'do_not_care'=>'Do not care about session, use cache anyway',
              'cache_by_session'=>'Use cached versions by used session variables',
        ),
    ),
    'sp_cache_disabled_substrings' => (object) array(
        'title' => 'URL contain substring',
        'description' => 'URL with these substrings will not be cached.',
    ),

    // Speed > Compression

    'sp_mod_gzip_ext' => (object) array(
        'title' => 'Enable by file extension',
        'description' => 'To all selected files will be used gzip compression if it is possible in visitor web browser (and your server). Basicaly it is something like zip or rar.',
    ),
    'sp_mod_minify' =>  (object) array(
        'title' => 'Reducing by file extension',
        'description' => 'All selected files will be reduced in size. It removes empty lines and redundant spaces. In htm mode it does not reduce size in &lt;script&gt; and &lt;pre&gt; tags.' ,
    ),

    // Speed > Browser Cache

    'sp_mod_expires_ext' => (object) array(
        'title' => 'Set expiration date to file extension',
        'description' => '',
    ),
    'sp_mod_expires_time' => (object) array(
        'title' => 'Expiration time',
        'description' => 'Files with chosen extension will be flagged by header "Expires" with computed date (or time) selected by this option.',
        'option'=>array(
              0=>'Disable',
              604800=>'Week <strong>(Recomended)</strong>',
              1209600=>'2 Weeks',
              2419200=>'4 Weeks',
              31536000=>'Year',
        ),
    ),


    // Speed > Fast 404

    'sp_fast404' => (object) array(
        'title' => 'Enable Fast 404 for files',
        'description' => 'Whole Wordpress (MySQL database and PHP scripts) is reloaded for every missing file such as image, javascript or css.
                          You may disable it by options above. User will see just simple html, not whole Wordpress.
                          <br /><br />
                          Everything will be also saved in <a href="admin.php?page=sp_seo&subpage=404">404 list</a>.',
    ),

    // Security > Main settings

    'sp_WP_admin_allowed_IPs_on' => (object) array(
        'title' => 'WP Login to admin section',
        'description' => 'Allow only these IPs:',
        'option'=>array(
              0=>'Do not care',
              1=>'Choose IPs',
        ),
    ),
    'sp_WP_admin_allowed_IPs_list' => (object) array(
        'title' => 'IPs',
        'description' => 'Your actual IP address is now: '.$_SERVER['REMOTE_ADDR'],
    ),

    // Security > Minimal Antispam

    'sp_enable_miniantispam' => (object) array(
        'title' => 'Antispam Mode',
        'description' => 'Please check also <a href="./options-discussion.php">Settings &gt; Discussion &gt; Comment Moderation</a>.
                          To be 100% buletproof choose Hold a comment in the queue if it contains <strong>1</strong> or more links.',
        'option'=>array(
              'off'=>'Do not use Mini Antispam',
              'change_name'=>'Use Mini Antispam and do not hide "Website" field in comment form',
              'disable_website_field'=>'Use Mini Antispam and hide "Website" field',
        ),
    ),

    'sp_enable_miniantispam_referer' => (object) array(
        'title' => 'Antispam Referer Check',
        'description' => 'Every send comment will be checked, if it has HTTP referer. HTTP referer is Page, that comenter visited before.
                          But it may cause problems, when comenter uses anonymous browsing.',
        'option'=>array(
              0=>'Do not use',
              1=>'Check referer page.',
        ),
    ),

    // Security > Censhorship

    'sp_disable_bad_words' => (object) array(
        'title' => 'Bad words check',
        'description' => 'See <a href="'.SP_PLUGIN_URL.'others/bad_words.txt" target="_blank"> them all.</a><br />'.
                         'See <a href="http://outsports.com/nfl/2005/0301nflshopnaughtywords.htm">orginal source</a>',
        'option'=>array(
              0=>'Off',
              1=>'Disable bad words from list',
        ),
    ),

    'sp_disable_very_long_words' => (object) array(
        'title' => 'Very large words',
        'description' => 'See <a href="#" onclick="document.getElementById(\'word_30\').style.display=\'block\';return false"> how much it is.</a>'.
                         '<div class="description" id="word_30" style="border: 1px solid rgb(223, 223, 223); padding: 5px 10px; border-top-left-radius: 3px; border-top-right-radius: 3px; border-bottom-right-radius: 3px; border-bottom-left-radius: 3px; display: none; ">
                            <q>
                              <a href="#" style="text-align:right;display:block;float:right" onclick="document.getElementById(\'word_30\').style.display=\'none\';return false">hide</a>
                              This -any-favourite-singer- is so niceeeeeeeeeee!!!!!!!!!!!!!<br>
                              50: I love youuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuu<br>
                              100: I love youuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuuu
                              </q>.
                          </div>',
        'option'=>array(
              'off'=>'Off',
              50=>'Disable comment if somebody sends word larger than 50 letters',
              100=>'Disable comment if somebody sends word larger than 100 letters',
        ),
    ),

    'sp_disable_very_long_comments' => (object) array(
        'title' => 'Very large comments',
        'description' => 'See <a href="#" onclick="document.getElementById(\'letters_count_2000\').style.display=\'block\';return false">how much it is!</a>'.
                         '<div class="description" id="letters_count_2000" style="border: 1px solid rgb(223, 223, 223); padding: 5px 10px; border-top-left-radius: 3px; border-top-right-radius: 3px; border-bottom-right-radius: 3px; border-bottom-left-radius: 3px; display: none; ">
                            <q>
                              <a href="#" style="text-align:right;display:block;float:right" onclick="document.getElementById(\'letters_count_2000\').style.display=\'none\';return false">hide</a>
                              Lorem ipsum dolor sit amet consectetuer In tellus parturient urna et. Tellus est est auctor elit Donec nulla eleifend massa id nulla. Mauris ut euismod lacinia Curabitur Sed nunc neque gravida dui faucibus. Vel a Aenean Maecenas id vitae in lorem libero euismod urna. Nibh sit ipsum tincidunt Aenean mus commodo orci Sed at magna. Morbi id Vivamus congue tincidunt hac sapien auctor ante Fusce elit. Phasellus Suspendisse turpis ante ac Sed Phasellus sagittis Quisque volutpat eu. Id Donec justo tellus id est dictumst condimentum metus pellentesque lorem. Lacus neque est neque Pellentesque dignissim sodales Aenean nulla nec penatibus. Tempus tellus Duis nulla id facilisis id mattis Aenean augue fames. Porta lacinia ridiculus Curabitur et ut lorem nec Aliquam tortor turpis. Donec tempor elit tincidunt wisi ante porttitor orci laoreet consequat id. Pretium montes Vestibulum pellentesque lobortis pretium dolor consequat congue a tincidunt. Quis dignissim consectetuer mauris arcu magna leo ris.
                              <br />
                              <br />
                              Lorem ipsum dolor sit amet consectetuer In tellus parturient urna et. Tellus est est auctor elit Donec nulla eleifend massa id nulla. Mauris ut euismod lacinia Curabitur Sed nunc neque gravida dui faucibus. Vel a Aenean Maecenas id vitae in lorem libero euismod urna. Nibh sit ipsum tincidunt Aenean mus commodo orci Sed at magna. Morbi id Vivamus congue tincidunt hac sapien auctor ante Fusce elit. Phasellus Suspendisse turpis ante ac Sed Phasellus sagittis Quisque volutpat eu. Id Donec justo tellus id est dictumst condimentum metus pellentesque lorem. Lacus neque est neque Pellentesque dignissim sodales Aenean nulla nec penatibus. Tempus tellus Duis nulla id facilisis id mattis Aenean augue fames. Porta lacinia ridiculus Curabitur et ut lorem nec Aliquam tortor turpis. Donec tempor elit tincidunt wisi ante porttitor orci laoreet consequat id. Pretium montes Vestibulum pellentesque lobortis pretium dolor consequat congue a tincidunt. Quis dignissim consectetuer mauris arcu magna leo ris.
                              <br />
                              <b>- 2000 Letters -</b>
                              <br />
                              <br />
                              Lorem ipsum dolor sit amet consectetuer In tellus parturient urna et. Tellus est est auctor elit Donec nulla eleifend massa id nulla. Mauris ut euismod lacinia Curabitur Sed nunc neque gravida dui faucibus. Vel a Aenean Maecenas id vitae in lorem libero euismod urna. Nibh sit ipsum tincidunt Aenean mus commodo orci Sed at magna. Morbi id Vivamus congue tincidunt hac sapien auctor ante Fusce elit. Phasellus Suspendisse turpis ante ac Sed Phasellus sagittis Quisque volutpat eu. Id Donec justo tellus id est dictumst condimentum metus pellentesque lorem. Lacus neque est neque Pellentesque dignissim sodales Aenean nulla nec penatibus. Tempus tellus Duis nulla id facilisis id mattis Aenean augue fames. Porta lacinia ridiculus Curabitur et ut lorem nec Aliquam tortor turpis. Donec tempor elit tincidunt wisi ante porttitor orci laoreet consequat id. Pretium montes Vestibulum pellentesque lobortis pretium dolor consequat congue a tincidunt. Quis dignissim consectetuer mauris arcu magna leo ris.
                              <br />
                              <br />
                              Lorem ipsum dolor sit amet consectetuer In tellus parturient urna et. Tellus est est auctor elit Donec nulla eleifend massa id nulla. Mauris ut euismod lacinia Curabitur Sed nunc neque gravida dui faucibus. Vel a Aenean Maecenas id vitae in lorem libero euismod urna. Nibh sit ipsum tincidunt Aenean mus commodo orci Sed at magna. Morbi id Vivamus congue tincidunt hac sapien auctor ante Fusce elit. Phasellus Suspendisse turpis ante ac Sed Phasellus sagittis Quisque volutpat eu. Id Donec justo tellus id est dictumst condimentum metus pellentesque lorem. Lacus neque est neque Pellentesque dignissim sodales Aenean nulla nec penatibus. Tempus tellus Duis nulla id facilisis id mattis Aenean augue fames. Porta lacinia ridiculus Curabitur et ut lorem nec Aliquam tortor turpis. Donec tempor elit tincidunt wisi ante porttitor orci laoreet consequat id. Pretium montes Vestibulum pellentesque lobortis pretium dolor consequat congue a tincidunt. Quis dignissim consectetuer mauris arcu magna leo ris.
                              <br />
                              <b>- 4000 Letters -</b>
                            </q>.
                          </div>',
        'option'=>array(
              'off'=>'Off',
              2000=>' Disable comment if somebody sends comment larger than 2000 letters',
              4000=>' Disable comment if somebody sends comment larger than 4000 letters',
        ),
    ),

    'sp_disable_very_newline_comments' => (object) array(
        'title' => 'Every word on new line',
        'description' => 'This rule is not applied, when there are only 5 newlines. Check "Off" if this is your poet\'s blog!<br />
                          See <a href="#" onclick="document.getElementById(\'new_lines_5\').style.display=\'block\';return false"> how much it is.</a>'.
                         '<div id="new_lines_5" style="border: 1px solid rgb(223, 223, 223); padding: 5px 10px; border-top-left-radius: 3px; border-top-right-radius: 3px; border-bottom-right-radius: 3px; border-bottom-left-radius: 3px; display: none; ">
                            <q>
                              <a href="#" style="text-align:right;display:block;float:right" onclick="document.getElementById(\'new_lines_5\').style.display=\'none\';return false">hide</a>
                              <b>New line is roughly after every 5 letters:</b><br>
                              Lorem<br>ipsum<br>dolor<br>
                              <br><b>New line is roughly after every 10 letters:</b><br>
                              In telluru<br>parturient<br>urnaet Tel<br>
                              <br><b>New line is roughly after every 20 letters:</b><br>
                              Tellus est quesumium<br>elit Donec nulla nun<br>fend massa id et quo
                            </q>.
                          </div>',
        'option'=>array(
              'off'=>'Off',
              5=>'Disable comment if somebody sends comment where new line is roughly after every 20 letters',
              10=>'Disable comment if somebody sends comment where new line is roughly after every 10 letters',
              20=>'Disable comment if somebody sends comment where new line is roughly after every 5 letters',
        ),
    ),

    // Sweep > Code

    'sp_wp_remove_metas' => (object) array(
        'title' => 'Remove WP metas',
        'description' => 'You may hide by removing WP metas that you use Wordpress. It will be more tough to hack or spam your page.',
        'valuetitles' => array(
            'l10n' => 'Remove the script, which is mostly used for scripts that send over localization data from PHP to the JS.',
            'rsd_link' => 'Remove the link to the Really Simple Discovery service endpoint, EditURI link',
            'wlwmanifest_link' => 'Remove the link to the Windows Live Writer manifest file',
            'adjacent_posts_rel_link_wp_head' => 'Remove the relational links for the posts adjacent to the current post',
            'wp_generator' => 'Remove the WordPress version i.e. - WordPress 3.3.2',
            'feed_links' => 'Remove links to the general feeds: Post and Comment Feed',
            'feed_links_extra' => 'Remove the links to the extra feeds such as category feeds',
            'index_rel_link' => 'Remove the index link',
        ),
    ),

    'sp_into_head_insert_code' => (object) array(
        'title' => 'Insert into &lt;HEAD&gt; html code',
        'description' => 'Please, don\'t insert here php code. It will not work.<br />Here you may insert f.e. your Google Analytics code or f.e. JS and CSS for some galery plugin.',
    ),

    'sp_before_body_end_insert_code' => (object) array(
        'title' => 'Insert before &lt;/BODY&gt; html code',
        'description' => 'Please, don\'t insert here php code. It will not work.',
    ),

    // Sweeping > Forbiden types

    'sp_forbidden_attachment' => (object) array(
        'title' => 'Attachment(s) pages',
        'description' => 'Attachment(s) pages are often invisible for normal page visitor and sometimes even for you. This may be used by spammers and hackers.',
        'option'=>array(
            ''=>'Leave it',
            'page'=>'Redirect attachment page to parent post or page',
            'image'=>'Redirect attachment page to attachment (just image or pdf file)',
            'imageRedir'=>'Redirect attachment page to attachment (just image or pdf file) and add 301 redirect to redirection if detected.',
            '404'=>'Show 404 Page Not Found',
        ),
    ),

    'sp_forbidden_day' => (object) array(
        'title' => 'Archives for days',
        'description' => 'If you write one post per week, I recomend you to redirect it. But I am also recomend to remove Calendar widgets.',
        'option'=>array(
            ''=>'Leave it',
            'month'=>'Redirect archive for day to archive for month',
            '404'=>'Show 404 Page Not Found',
            'fast404'=>'Show fast 404',
        ),
    ),

    'sp_forbidden_author' => (object) array(
        'title' => 'Author(s) pages',
        'description' => 'If you are only one author here and this is blog only for you, I recomend you to redirect it.',
        'option'=>array(
            ''=>'Leave it',
            'home'=>'Redirect author page to home',
            '404'=>'Show 404 Page Not Found',
            'fast404'=>'Show fast 404',
        ),
    ),

    'sp_forbidden_special_feeds' => (object) array(
        'title' => 'Feeds',
        'description' => 'Those feeds are for: author,day,attachment and search feeds, Vitamin is unable to remove attachment feeds.',
        'option'=>array(
            ''=>'Leave it all: author,day,attachment and search feeds',
            '404'=>'Show 404 Page Not Found',
            'fast404'=>'Show fast 404 (unable to set it for attachment feeds)',
        ),
    ),

);