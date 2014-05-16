<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource\Interceptor;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Exception\Uri;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Inject\NamedArgsInject;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\NamedArgsInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

final class EmbedInterceptor implements MethodInterceptor
{
    use NamedArgsInject;

    /**
     * @var \BEAR\Resource\ResourceInterface
     */
    private $resource;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    const LINK_ANNOTATION = 'BEAR\Resource\Annotation\Link';

    /**
     * @param ResourceInterface $resource
     *
     * @Inject
     */
    public function __construct(ResourceInterface $resource, AnnotationReader $reader, NamedArgsInterface $namedArgs)
    {
        $this->resource = $resource;
        $this->reader = $reader;
        $this->namedArgs = $namedArgs;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {

        $result =  $invocation->proceed();

        $resourceObject = $invocation->getThis();
        $method = $invocation->getMethod();
        $query = $this->namedArgs->get($invocation);

        $links = $this->reader->getMethodAnnotations($method);
        foreach ($links as $link) {
            if (! $link instanceof Link) {
                continue;
            }
            /** $link @var Link */
            try {
                $uri = \GuzzleHttp\uri_template($link->href, $query);
                $resourceObject->body[$link->rel] = $this->resource->get->uri($uri)->request();
            } catch (Uri $e) {
                throw new \LogicException('Invalid @Link:' . $link->uri, 500, $e);
            }
        }
        return $result;
    }
}
