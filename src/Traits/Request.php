<?php

namespace cccdl\tencentSdk\Traits;

use GuzzleHttp\Client;

trait Request
{
    public function getNew()
    {
        return new static;
    }
}