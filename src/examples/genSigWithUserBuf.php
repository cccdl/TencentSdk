<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Im\Im;

try {

    $appId = '';

    $key = '';

    $im = new Im($appId, $key);

    $sign = $im->genSigWithUserBuf('1000001', 'a');

    echo $sign;

} catch (cccdlException $e) {
    echo $e->getMessage();
}