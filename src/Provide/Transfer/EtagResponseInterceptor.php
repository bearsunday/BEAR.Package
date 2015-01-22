<?php
/**
 * This file is part of the *** package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Transfer;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class EtagResponseInterceptor implements MethodInterceptor
{
    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $server = $invocation->getArguments()[1];
        $headers = $invocation->getThis()->headers;
        if (isset($headers['Etag']) && isset($server['HTTP_IF_NONE_MATCH']) && stripslashes($server['HTTP_IF_NONE_MATCH']) === $headers['Etag']) {
            goto NOT_MODIFIED_304;
        }
        if (isset($headers['Last-Modified']) && isset($server['HTTP_IF_MODIFIED_SINCE']) && $server['HTTP_IF_MODIFIED_SINCE'] === $headers['Last-Modified']) {
            goto NOT_MODIFIED_304;
        }

        return $invocation->proceed();

        NOT_MODIFIED_304: {
            http_response_code(304);
            header('Cache-Control: public');
        }
    }
}
