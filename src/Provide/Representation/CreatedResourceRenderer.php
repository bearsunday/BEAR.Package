<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Exception\LocationHeaderRequestException;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterInterface;

/**
 * 201 CreatedResource renderer
 */
class CreatedResourceRenderer implements RenderInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ResourceInterface
     */
    private $resource;

    public function __construct(RouterInterface $router, ResourceInterface $resource)
    {
        $this->router = $router;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $url = parse_url((string) $ro->uri);
        $locationUri = sprintf('%s://%s%s', $url['scheme'], $url['host'], $ro->headers['Location']);
        try {
            $locatedResource = $this->resource->uri($locationUri)();
            /* @var $locatedResource ResourceObject */
        } catch (\Exception $e) {
            $ro->code = 500;
            $ro->view = '';

            throw new LocationHeaderRequestException($locationUri, 0, $e);
        }
        $this->updateHeaders($ro);
        $ro->view = $locatedResource->toString();

        return $ro->view;
    }

    private function getReverseMatchedLink(string $uri) : string
    {
        $urlParts = parse_url($uri);
        $routeName = $urlParts['path'];
        isset($urlParts['query']) ? parse_str($urlParts['query'], $value) : $value = [];
        if ($value === []) {
            return $uri;
        }
        $reverseUri = $this->router->generate($routeName, $value);
        if (\is_string($reverseUri)) {
            return $reverseUri;
        }

        return $uri;
    }

    private function updateHeaders(ResourceObject $ro)
    {
        $ro->headers['content-type'] = 'application/hal+json';
        if (isset($ro->headers['Location'])) {
            $ro->headers['Location'] = $this->getReverseMatchedLink($ro->headers['Location']);
        }
    }
}
