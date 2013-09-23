<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource\Interceptor;

use BEAR\Resource\Exception\Uri;
use BEAR\Resource\ResourceInterface;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Doctrine\DBAL\DriverManager;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Resource graph interceptor
 */
final class ResourceGraph implements MethodInterceptor
{
    /**
     * Constructor
     *
     * @param ResourceInterface $resource
     *
     * @Inject
     */
    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $page = $invocation->getThis();
        if (! is_array($page->body)) {
            return $invocation->proceed();
        }

        foreach ($page->body as $slot => $uri) {
            try {
                $page->body[$slot] = $this->resource->get->uri($uri)->request();
            } catch (Uri $e) {
            }
        }
        return $invocation->proceed();
    }
}
