<?php

namespace Sandbox\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Package\Module\WebContext\WebContextProvider;
use Sandbox\Interceptor\BasicAuth\CertificateAuthorityInterface;

/**
 * Basic Auth interceptor
 */
class BasicAuthInterceptor implements MethodInterceptor
{
    /**
     * Certificate authority
     *
     * @var CertificateAuthorityInterface
     */
    private $ca;

    /**
     * Web context provider
     *
     * @var WebContextProvider
     */
    private $webContextProvider;

    /**
     * Constructor
     *
     * @Inject
     * @Named("webContextProvider=webContext")
     */
    public function __construct(CertificateAuthorityInterface $ca, ProviderInterface $webContextProvider)
    {
        $this->ca = $ca;
        $this->webContextProvider = $webContextProvider;
    }

    /**
     * @param MethodInvocation $invocation Method Invocation
     */
    public function invoke(MethodInvocation $invocation)
    {
        $webContext = $this->webContextProvider->get();

        $user = $webContext->getServer('PHP_AUTH_USER');
        $passwd = $webContext->getServer('PHP_AUTH_PW');

        if ($user === null) {
            return $this->unauthorized($invocation);
        }

        $auth = $this->ca->auth($user, $passwd);
        if ($auth) {
            return $invocation->proceed();
        }

        return $this->unauthorized($invocation);
    }

    /**
     * Procss on unauthorized
     *
     * @param MethodInvocation $invocation Method Invocation
     * @return ResourceObject
     */
    private function unauthorized(MethodInvocation $invocation)
    {
        $resource = $invocation->getThis();

        $resource->code = 401;
        $resource->headers['WWW-Authenticate'] = 'Basic realm="Please Enter Your Password."';
        $resource->headers['Content-type'] = 'text/html; charset=UTF-8';
        $resource->body = '<!DOCTYPE html><html><head><title>Authentication Failed.</title></head><body>Authentication Failed</body></html>';

        return $resource;
    }
}
