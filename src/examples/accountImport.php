<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Im\imOpenLoginSvc;

try {

    //IM appid
    $appId = '';

    //IM key
    $key = '';

    // 用户id
    $identifier = '';

    $im = new imOpenLoginSvc($appId, $key, $identifier);

    $im->accountImport('1111111', 'niho', '/avatar.png');

} catch (cccdlException $e) {
}

