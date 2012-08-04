<?php

class spHooks {

    public $globalChangeDone;
    public $settings;

    function loadWPHooks(){
        $this->globalChangeDone = false;

        add_action('login_init', array($this, 'login_init') );

        add_action('wp_headers',array($this, 'change_http_headers'));

        add_action('template_redirect', array($this, 'disable_kinds_of_pages'));

        add_action('template_redirect', array($this, 'disable_kinds_of_feeds'));

        add_action('wp_head',array($this, 'change_head_metas_and_code'), 1);

        add_action('wp_footer', array($this, 'change_footer_code'), 10000);
        
        // Sitemaps + Cache

        add_action( 'admin_init', array($this, 'onChangeInAdmin'), 10000);

        add_action( 'save_post',     array($this, 'doGlobalUpdate') );
        add_action( 'edit_post',     array($this, 'doGlobalUpdate') );
        add_action( 'publish_post',  array($this, 'doGlobalUpdate') );
        add_action( 'delete_post',   array($this, 'doGlobalUpdate') );

        add_action( 'comment_post',    array($this, 'reactOnComment'), 1000 );
        add_action( 'edit_comment',    array($this, 'reactOnComment'), 1000 );
        add_action( 'deleted_comment', array($this, 'reactOnComment'), 1000 );
        add_action( 'trashed_comment', array($this, 'reactOnComment'), 1000 );
        
    }

    function __construct( ){
        $this->loadWPHooks();
        $this->settings = spClasses::get('Settings');
    }
    
    function login_init(){

        if( $this->settings->WP_admin_allowed_IPs_on ){
            global $_SERVER;
            if( in_array($_SERVER['REMOTE_ADDR'], $this->settings->WP_admin_allowed_IPs_list ) ){
                return;
            }else{
                require_once SP_PLUGIN_ROOT.'classes/spError.php';
                new spError('Not Allowed IP', 'You may not log in. <br /><br />Your IP address <strong>'.$_SERVER['REMOTE_ADDR'].'</strong> is not on list of allowed IPs.');
                exit;
            }
        }
        return;
    }
    
    function change_http_headers(){
        if( function_exists('header_remove') ){
            header_remove('Expires');
            header_remove('Link');
            header_remove('Pragma');
            header_remove('Server');
            header_remove('X-Pingback');
        }

        header("Cache-Control: private, must-revalidate", true);
        header("Content-Type: text/html; charset=UTF-8", true);
        header("Vary: Accept-Encoding", true);
        header("Content-Language: " . get_bloginfo('language'), true);
    }
    
    function disable_kinds_of_pages(){
        // source and inspiration on http://betterwp.net/wordpress-tips/disable-some-wordpress-pages/

        global $wp_query, $post;

        if( is_attachment() ){
            if( empty($this->settings->forbidden_attachment) ){
                return;
            }

            if( ( '404' == $this->settings->forbidden_attachment ) ){
                $wp_query->set_404();
                return;
            }
            
            $attachment = get_query_var('attachment');
            $attachment = (empty($attachment)) ? get_query_var('attachment_id') : $attachment;
            $attachment = get_post($ID);

            if( ( 'page' == $this->settings->forbidden_attachment ) ){
                header( 'location: '. get_permalink( $attachment->post_parent ) );
                exit;
            }
            
            if( ( 'image' == $this->settings->forbidden_attachment ) ){
                header ( 'location: '. $attachment->guid );
                exit;
            }

            if( ( 'imageRedir' == $this->settings->forbidden_attachment ) ){

                $spStats = spClasses::get('Stats');
                $spStats->save(301);

                $spRedirs = spClasses::get('Redirs');
                $spRedirs->addOrReplace( array(
                      'priority' => 9,
                      'orgUrl' => get_permalink( $attachment ),
                      'code' => 301,
                      'redirUrl' => $attachment->guid
                ) );
                header ( 'location: '. $attachment->guid );
                exit;
            }
        }

        if( is_day() ){
            if( ( '404' == $this->settings->forbidden_day ) ){
                $wp_query->set_404();
                return;
            }
        }

        if( is_author() ){
            if( ( '404' == $this->settings->forbidden_author ) ){
                $wp_query->set_404();
                return;
            }
        }
    }
    
    function disable_kinds_of_feeds(){
        // source and inspiration on http://betterwp.net/wordpress-tips/disable-some-wordpress-pages/

        global $wp_query;

        if( ( '404' == $this->settings->forbidden_special_feeds ) or ( 'fast404' == $this->settings->forbidden_special_feeds ) ){
            if( is_feed()) {
                $author     = get_query_var('author_name');
                $attachment = get_query_var('attachment');
                $attachment = (empty($attachment)) ? get_query_var('attachment_id') : $attachment;
                $day        = get_query_var('day');
                $search     = get_query_var('s');

                if (!empty($author) or !empty($attachment) or !empty($day) or !empty($search)) {
                    $wp_query->set_404();
                    $wp_query->is_feed = false;
                }
            }
        }
    }
    
    function change_head_metas_and_code(){
        if( in_array('l10n', $this->settings->wp_remove_metas) )                            wp_deregister_script('l10n');
        if( in_array('rsd_link', $this->settings->wp_remove_metas) )                        remove_action('wp_head', 'rsd_link');
        if( in_array('wlwmanifest_link', $this->settings->wp_remove_metas) )                remove_action('wp_head', 'wlwmanifest_link');
        if( in_array('adjacent_posts_rel_link_wp_head', $this->settings->wp_remove_metas) ) remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
        if( in_array('wp_generator', $this->settings->wp_remove_metas) )                    remove_action('wp_head', 'wp_generator');
        if( in_array('feed_links', $this->settings->wp_remove_metas) )                      remove_action('wp_head', 'feed_links');
        if( in_array('feed_links_extra', $this->settings->wp_remove_metas) )                remove_action('wp_head', 'feed_links_extra', 3);
        if( in_array('index_rel_link', $this->settings->wp_remove_metas) )                  remove_action('wp_head', 'index_rel_link' );

        $head_html_code = trim( get_option( 'sp_into_head_insert_code', '' ) );
        if( ! empty( $head_html_code ) ){
            echo $head_html_code;
        }
    }
    
    function change_footer_code(){
        $footer_html_code = trim( get_option( 'sp_before_body_end_insert_code', '' ) );
        if( ! empty( $footer_html_code ) ){
            echo $footer_html_code;
        }
    }
    
    function onChangeInAdmin(){

        global $_POST;
        global $_GET;

        if( 'wp-admin/themes.php' == substr($_SERVER["PHP_SELF"], -19) ){
            if( isSet($_GET['activated']) ){
                $this->doGlobalCacheUpdate();
                return;
            }
        }

        if( empty( $_POST ) or ( 0 == count($_POST)) ){
            return;
        }

        if( !empty( $_POST['action'] ) ){
            switch ($_POST['action']) {
                case 'autosave': return;
                case 'wp-remove-post-lock': return;
            }
        }

        if( 'wp-admin/options.php' == substr($_SERVER["PHP_SELF"], -20) ){
            $this->doGlobalCacheUpdate();
            return;
        }

        if( 'wp-admin/admin-ajax.php' == substr($_SERVER["PHP_SELF"], -23) ){
            $this->doGlobalUpdate();
            return;
        }

        if( 'wp-admin/plugins.php' == substr($_SERVER["PHP_SELF"], -15) ){
            if( !empty( $_GET['activate'] ) ){
                $this->doGlobalUpdate();
            }
        }

        if( 'wp-admin/media.php' == substr($_SERVER["PHP_SELF"], -18) ){
            if( !empty($_GET['attachment_id']) ){
                $this->doGlobalUpdate( 1*$_GET['attachment_id'] );
            }
            return;
        }

        if ( !empty( $_POST['tax_input'] ) and 'tax_input' == $_POST['tax_input'] ) {
            $this->doGlobalUpdate();
            return;
        }
        
        if( !empty($_POST['action']) ) {
            if( ( 'inline-save-tax' == $_POST['action'] ) or ( 'editedtag' == $_POST['action'] ) or ( 'delete-tag' == $_POST['action'] ) ){
                $this->doGlobalUpdate();
            }else if( !empty ($_POST['taxonomy'] ) ){
                $this->doGlobalUpdate();
            }
            return;
        }
    }
    
    function onChangeAttachment( $attachmentID ){
        if( empty( $_POST ) or ( 0 == count($_POST)) ){ return; }

        $attachment = get_post( $attachmentID, 'OBJECT');

        if( empty( $attachment ) ){ return; }
        if( empty( $attachment->post_parent ) ){ return; }

        $this->doLocalCacheUpdate( get_permalink($attachment->ID) );

        $page = get_post( $attachment->post_parent, 'OBJECT');

        if( empty( $page ) ){ return; }
        
        $this->doLocalCacheUpdate( get_permalink($page->ID) );
        // $attachment->post_parent ;
    }
    
    function doLocalCacheUpdate($link){
        $spCache = spClasses::get('Cache');
        $spCache->deleteCacheFileByURL($link);
    }

    function doGlobalCacheUpdate(){
        if( $this->globalChangeDone ) return;
        $this->globalChangeDone = true;

        $spCache = spClasses::get('Cache');
        $spCache->deleteAllInCache();
    }

    function doGlobalUpdate(){

        $this->doGlobalCacheUpdate();
        
        if( is_admin() ){
            require_once 'spSitemapsAdmin.php';
            $_sp_SitemapsAdmin = new spSitemapsAdmin();
            $_sp_SitemapsAdmin->updateIndexSitemap();
            if( !empty( $_sp_SitemapsAdmin->dataList->data ) ){
                foreach($_sp_SitemapsAdmin->dataList->data as $name=>$description) {
                    $_sp_SitemapsAdmin->updateSitemap($description);
                }
            }
        }
    }
    
    function reactOnComment( $commentID ){
        if( empty($this->settings->cache_reaction_on_comments) ){
            $this->doGlobalCacheUpdate();
        }else{
            $comment = get_comment( $commentID );
            $page = get_post($comment->comment_post_ID );
            $this->doLocalCacheUpdate( get_permalink($page->ID) );
        }
    }
}
