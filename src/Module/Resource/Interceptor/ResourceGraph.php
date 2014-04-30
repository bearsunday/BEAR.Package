<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource\Interceptor;

use BEAR\Resource\Exception\Uri;
use BEAR\Resource\ResourceInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

final class ResourceGraph implements MethodInterceptor
{
    /**
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
