<?php

namespace BEAR\Package\Provide\Transfer;

function http_response_code($int)
{
    EtagResponseInterceptorTest::$code = func_get_args();
}
