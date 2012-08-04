<?php

class spAntispam {

    public $settings;

    function __construct(){
        $this->settings = spClasses::get('Settings');
        
        if( ! function_exists('add_action') ){
            // WP is NOT loaded
            $this->careaboutReferer();
            $this->careaboutWebsiteField();
            $this->careaboutAntiIdioticCommentRules();
            $this->careaboutForbiddenWords();
        }
    }
    
    function careaboutReferer(){
        global $_SERVER;
        if( empty($this->settings->enable_miniantispam_referer) ){
            return;
        }
        if( empty($_SERVER["HTTP_REFERER"]) ){
            require 'spError.php';
            header('HTTP/1.1 403 Forbidden');
            new spError('Disabled comments', 'Comments are disabled for users, who use anonymous browsing.');
        }
    }
    
    function careaboutWebsiteField(){
        if( 'off' == $this->settings->enable_miniantispam ){
            return;
        }

        global $_POST;

        if( isSet($_POST['url']) ){
            require 'spError.php';
            header('HTTP/1.1 403 Forbidden');
            new spError('Disabled comments', 'You were disabled as spammer.');
        }
        
        if( 'change_name' == $this->settings->enable_miniantispam ){
            $_POST['url'] = $_POST['url_renamed_by_mini_antispam'];
        }
    }

    function cleanFromCommentFormValues($data){

        if( 'change_name' == $this->settings->enable_miniantispam ){
            $data = str_replace("'url'","'url_renamed_by_mini_antispam'",$data);
            $data = str_replace('"url"','"url_renamed_by_mini_antispam"',$data);
        }

        if( 'disable_website_field' == $this->settings->enable_miniantispam ){
            $data = str_replace("name='url'","",$data);
            $data = str_replace('name="url"','',$data);
            $data = str_replace('name=url',  '',$data);
        }
        return $data;
    }

    function careaboutAntiIdioticCommentRules(){
        global $_POST;
        
        if( empty($_POST) ) return;
        if( empty($_POST['comment']) ) return;

        if( 'off' != $this->settings->disable_very_long_words ){
            $cc = 0;
            $max = 1 * $this->settings->disable_very_long_words;
            for($i=0;$i<strlen($_POST['comment']);$i++) {
                $cc ++;
                $ch = $_POST['comment'][$i];
                if( in_array($ch, array("\n","\t"," ",".",",","#","\\","-","_","%","/","<",">") ) ) {
                    $cc = 0;
                }
                if($cc > $max){
                    require 'spError.php';
                    header('HTTP/1.1 403 Forbidden');
                    new spError('Disabled comments', 'Please, do not send messages, how much you love Justin Bieber. Coments like "I love you soooooooooooooooooooooooooooooooooooooooooooooooooo much Justin!!!!!!" are forbidden.<br /><br />Normal people write normal (not extremly long) words in sentences.');
                }
            }
        }

        if( 'off' != $this->settings->disable_very_long_comments ){
            if( strlen($_POST['comment']) > 1 * $this->settings->disable_very_long_comments ){
                require 'spError.php';
                header('HTTP/1.1 403 Forbidden');
                new spError('Disabled comments', 'Please, do not send soooo looooong comments.');
            }
        }

        if( 'off' != $this->settings->disable_very_newline_comments ){
            $percent = $this->settings->disable_very_newline_comments;
            $nl_cnt = substr_count($_POST['comment'],"\n");
            if( $nl_cnt <= 5 ){
                if( $nl_cnt / strlen($_POST['comment']) > $percent / 100 ){
                    require 'spError.php';
                    header('HTTP/1.1 403 Forbidden');
                    new spError('Disabled comments', 'Please, write comments like a human. Less newlines is your goal.');
                }
            }
        }
    }
    
    function careaboutForbiddenWords(){
        if( empty( $this->settings->disable_bad_words ) ){
            return;
        }

        global $_POST;

        $bad_words = @file( dirname(dirname(__FILE__)).'/others/bad_words.txt' );
        
        if( empty($bad_words) ){
            return;
        }
        
        foreach ($bad_words as $key=>$value) {
            $bad_words[$key] = ' '.trim($value).' ';
        }
        
        $comment = $_POST['comment'];
        $comment = str_replace(array("\t","\n","\r",".",",","-","|","+"),' ',$comment);
        $comment = ' '.strtolower($comment).' ';
        
        $bad_word_finded = false;

        foreach ($bad_words as $key=>$word) {
            if( FALSE !== strpos($comment, $word) ){
                $bad_word_finded = true;
                break;
            }
        }
        
        if( ! $bad_word_finded ){
            return;
        }

        require 'spError.php';
        header('HTTP/1.1 403 Forbidden');
        new spError('Disabled comments', 'Please, do not use any bad words like:<pre>'. file_get_contents(( dirname(dirname(__FILE__)).'/others/bad_words.txt' )) .'</pre>');
    }
}
