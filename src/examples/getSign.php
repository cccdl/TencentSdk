<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Im\Im;

try {

    //IM appid
    $appId = '';

    //IM key
    $key = '';

    // 用户id
    $identifier = '';

    $im = new Im($appId, $key, $identifier);

    $sign = $im->getSign($identifier);

    echo $sign;

} catch (cccdlException $e) {
    echo $e->getCode();
    echo '----';
    echo $e->getMessage();
}