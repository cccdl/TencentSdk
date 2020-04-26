<?php

namespace cccdl\tencentSdk\Im;

use cccdl\tencentSdk\Auth;
use cccdl\tencentSdk\Traits\Request;

class Im extends Auth
{
    use Request;

    public function a()
    {
        echo $this->genSig();
    }
}