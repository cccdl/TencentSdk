<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Im\imOpenLoginSvc;

try {

    //IM appid
    $appId = '1400308341';

    //IM key
    $key = '9f1bdccf37831fa09463e92e93b9944ce92c970c29377182435307407b50bc05';

    // 用户id
    $identifier = 'long';

    $im = new imOpenLoginSvc($appId, $key, $identifier);

    $res = $im->accountImport('1000001', '这是个昵称', 'upload/avatar/1/1.jpg');

    var_dump($res);

} catch (cccdlException $e) {
}

