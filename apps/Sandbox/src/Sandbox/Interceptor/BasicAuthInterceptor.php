<?php

namespace Sandbox\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
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
     * Unauthorized message.
     *
     * This is sent as resource body when authorization failed.
     *
     * @var string
     */
    private $unauthorizedMessage = '401 Unauthorized';

    /**
     * Constructor
     *
     * @Inject
     * @Named("webContextProvider=webContext")
     */
    public function __construct(
        CertificateAuthorityInterface $ca,
        AnnotationReader $annotationReader,
        ProviderInterface $webContextProvider
    ) {
        $this->ca = $ca;
        $this->annotationReader = $annotationReader;
        $this->webContextProvider = $webContextProvider;
    }

    /**
     * Set Unauthorized message
     *
     * @param string $message
     * @Inject(optional = true)
     * @Named('http_response_405')
     */
    public function setUnauthorizedMessage($message)
    {
        $this->unauthorizedMessage = $message;
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
        $method = $invocation->getMethod();
        $annotation = $this->annotationReader->getMethodAnnotation($method, 'Sandbox\Annotation\Auth');
        $resource = $invocation->getThis();

        $resource->code = 401;
        $resource->headers['WWW-Authenticate'] = sprintf('Basic realm="%s"', $annotation->realm);
        $resource->headers['Content-type'] = 'text/html; charset=UTF-8';
        $resource->body = (string) $this->unauthorizedMessage;

        return $resource;
    }
}
