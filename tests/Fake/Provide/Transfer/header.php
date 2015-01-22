<?php

namespace BEAR\Package\Provide\Transfer;

function header(
    $string,
    $replace = true,
    $http_response_code = null
) {
    EtagResponseInterceptorTest::$headers[] = func_get_args();
}
