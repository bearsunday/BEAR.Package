<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Cache\Interceptor;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\Cache;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class CacheLoader implements MethodInterceptor
{
    use EtagTrait;

    /**
     * Pager key
     *
     * @var string
     */
    private $pagerKey = '_start';

    /**
     * Cache header key
     *
     * @var string
     */
    const HEADER_CACHE = 'x-cache';

    /**
     * @var array
     */
    private $get = [];

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @param array $get
     */
    public function setGet(array $get)
    {
        $this->get = $get;
    }

    /**
     * @param Cache            $cache
     * @param AnnotationReader $annotationReader
     *
     * @Inject
     */
    public function __construct(
        Cache $cache,
        AnnotationReader $annotationReader
    ) {
        $this->cache = $cache;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Set pager query key
     *
     * @param string $pagerKey
     *
     * @return $this
     * @Inject(optional=true)
     * @Named("pager_key")
     */
    public function setPagerQueryKey($pagerKey)
    {
        $this->pagerKey = $pagerKey;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $this->get = ($this->get) ?: $_GET;
        $ro = $invocation->getThis();
        /** @var $ro \BEAR\Resource\ResourceObject */
        $args = $invocation->getArguments();
        $id = $this->getEtag($ro, $args);
        $ro->headers['Tag'] = $id;
        $saved = $this->cache->fetch($id);
        $pagerNum = $this->getPagerNum($saved);
        if ($pagerNum) {
            $saved = (isset($saved['pager'][$pagerNum])) ? $saved['pager'][$pagerNum] : false;
        }
        if ($saved) {
            return $this->getSavedResource($invocation, $saved);
        }

        return $this->save($invocation, $pagerNum, $id);
    }

    /**
     * Return pager number
     *
     * @param $saved
     *
     * @return bool|int
     */
    private function getPagerNum($saved)
    {
        if (isset($this->get[$this->pagerKey])) {
            return $this->get[$this->pagerKey];
        } elseif (isset($saved['pager'])) {
            return 1;
        }
        return false;
    }

    /**
     * Return saved resource
     *
     * @param MethodInvocation $invocation
     * @param mixed            $saved
     *
     * @return object
     */
    private function getSavedResource(MethodInvocation $invocation, $saved)
    {
        $resource = $invocation->getThis();
        list($resource->code, $resource->headers, $resource->body) = $saved;
        $cache = json_decode($resource->headers[self::HEADER_CACHE], true);
        $resource->headers[self::HEADER_CACHE] = json_encode(
            [
                'mode' => 'R',
                'date' => $cache['date'],
                'life' => $cache['life']
            ]
        );

        return $resource;
    }

    /**
     * Save
     *
     * @param MethodInvocation $invocation
     * @param int              $pagerNum
     * @param string           $id
     *
     * @return object
     */
    private function save(MethodInvocation $invocation, $pagerNum, $id)
    {
        $invocation->proceed();
        $resource = $invocation->getThis();
        $time = $this->getSaveTime($invocation);
        $resource->headers[self::HEADER_CACHE] = json_encode(
            [
                'mode' => 'W',
                'date' => date('r'),
                'life' => $time
            ]
        );
        $data = [$resource->code, $resource->headers, $resource->body];
        if ($pagerNum) {
            $saved['pager'][$pagerNum] = $data;
            $data = $saved;
        }
        $this->cache->save($id, $data, $time);

        return $resource;
    }

    /**
     * Return TTL
     *
     * @param MethodInvocation $invocation
     *
     * @return int
     */
    protected function getSaveTime(MethodInvocation $invocation)
    {
        $annotation = $this->annotationReader->getMethodAnnotation($invocation->getMethod(), 'BEAR\Sunday\Annotation\Cache');
        $time = $annotation ? $annotation->time : null;

        return $time;
    }
}
