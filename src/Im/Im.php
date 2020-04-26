<?php

namespace cccdl\tencent_sdk\Im;

use cccdl\tencent_sdk\Auth;
use cccdl\tencent_sdk\Traits\Request;

class Im extends Auth
{
    use Request;

    public function a()
    {
        echo $this->genSig();
    }
}