<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use cccdl\tencentSdk\Exception\cccdlException;
use cccdl\tencentSdk\Im\Im;

try {

    $appId = '1400308341';

    $key = '1400308341';

    $im = new Im($appId, $key);

    var_dump($im);
    die;
} catch (cccdlException $e) {
    echo $e->getMessage();
}