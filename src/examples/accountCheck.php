<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/comfig.php';

use cccdl\tencent_sdk\Exception\cccdlException;
use cccdl\tencent_sdk\Im\imOpenLoginSvc;

try {


    $im = new imOpenLoginSvc($appId, $key, $identifier);

    $res = $im->accountCheck(['1000001', '1000002', '1000003', '1000004', '1000005']);

    var_dump($res['data']['ResultItem']);

} catch (cccdlException $e) {
    echo $e->getCode();
    echo '----';
    echo $e->getMessage();
}

