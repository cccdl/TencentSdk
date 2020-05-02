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

    $res = $im->accountImport('1000001', '这是个昵称', 'upload/avatar/1/1.jpg');

    var_dump($res);

} catch (cccdlException $e) {
}

