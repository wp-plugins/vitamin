<?php
require_once 'classes/spClasses.php';

$stats = spClasses::get('Stats');
$stats->save('403');

$r403 = spClasses::get('403s');
$r403->saveActualUrl();

require_once 'classes/spError.php';
header("Status: 403 Forbidden");
new spError('403','Forbidden by substring');