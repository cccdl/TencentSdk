<?php

namespace cccdl\tencent_sdk\Traits;


use cccdl\tencent_sdk\Exception\cccdlException;
use GuzzleHttp\Client;

trait Request
{
    protected static $timeout = 2;

    public static function post($url, $param)
    {
        $client = new Client(['timeout' => self::$timeout]);

        $response = $client->post($url, ['json' => $param]);

        $code = $response->getStatusCode();

        if ($response->getStatusCode() != 200) {
            throw new cccdlException('请求失败！', $code);
        }

        $body = json_decode($response->getBody(), true);

        if ($body['ActionStatus'] != 'OK') {
            throw new cccdlException($body['ErrorInfo'], $body['ErrorCode']);
        }

        return ['code' => $code, 'data' => json_decode($response->getBody(), true)];
    }
}