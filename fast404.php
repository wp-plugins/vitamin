<?php
require_once 'classes/spClasses.php';

$spRedirs = spClasses::get('Redirs');
if( $spRedirs->shouldBeApplied() ) $spRedirs->redirect();

$stats = spClasses::get('Stats');
$stats->save('404');

$r404 = spClasses::get('404s');
$r404->saveActualUrl();

require_once 'classes/spError.php';
header("HTTP/1.0 404 Not Found");

if( isSet($_GET['type']) ){
    switch ($_GET['type']) {
        case 'page':
            new spError('404','Page not found'); exit;
        case 'search_feed':
            new spError('404','Search feeds are disabled'); exit;
        case 'author_feed':
            new spError('404','Author feeds are disabled'); exit;
        case 'day_feed':
            new spError('404','Day feeds are disabled'); exit;
    }
}

new spError('404','File not found');