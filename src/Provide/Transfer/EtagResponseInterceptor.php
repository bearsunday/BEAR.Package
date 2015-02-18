<?php
/**
 * This file is part of the BEAR.Package package
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
        if ($this->isSameEtag($headers, $server) || $this->isNotModifiedSince($headers, $server)) {
            http_response_code(304);
            header('Cache-Control: public');

            return;
        }
        return $invocation->proceed();
    }

    /**
     * @param array $headers
     * @param array $server
     */
    private function isNotModifiedSince($headers, $server)
    {
        if (isset($headers['Last-Modified']) && isset($server['HTTP_IF_MODIFIED_SINCE']) && $server['HTTP_IF_MODIFIED_SINCE'] === $headers['Last-Modified']) {
            return true;
        }

        return false;
    }

    /**
     * @param array $headers
     * @param array $server
     *
     * @return array
     */
    private function isSameEtag($headers, $server)
    {
        if (isset($headers['Etag']) && isset($server['HTTP_IF_NONE_MATCH']) && stripslashes($server['HTTP_IF_NONE_MATCH']) === $headers['Etag']) {
             return true;
        }

        return false;
    }
}
