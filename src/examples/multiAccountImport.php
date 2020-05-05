<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Im\imOpenLoginSvc;

try {

    //IM appid
    $appId = '';

    //IM key
    $key = '';

    // 管理员账号
    $identifier = '';

    $im = new imOpenLoginSvc($appId, $key, $identifier);

    $res = $im->multiAccountImport(['1000001', '1000002']);

    var_dump($res);

} catch (cccdlException $e) {
    echo $e->getCode();
    echo '----';
    echo $e->getMessage();
}

