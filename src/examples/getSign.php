<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/comfig.php';

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Im\Im;

try {

    $im = new Im($appId, $key, $identifier);

    $sign = $im->getSign($identifier);

    echo $sign;

} catch (cccdlException $e) {
    echo $e->getCode();
    echo '----';
    echo $e->getMessage();
}