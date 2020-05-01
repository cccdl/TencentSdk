<?php

namespace cccdl\tencent_sdk\Traits;


use GuzzleHttp\Client;

trait Request
{
    protected static $timeout = 2;

    public static function post($url, $param)
    {
        $client = new Client(['timeout' => self::$timeout]);

        $response = $client->post($url, ['json' => $param]);

        return ['code' => $response->getStatusCode(), 'data' => json_decode($response->getBody(), true)];
    }
}