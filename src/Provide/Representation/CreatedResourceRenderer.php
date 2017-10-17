<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Exception\LocationHeaderRequestException;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Doctrine\Common\Annotations\Reader;

/**
 * 201 @CreatedResource renderer
 */
class CreatedResourceRenderer implements RenderInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @param Reader          $reader
     * @param RouterInterface $router
     */
    public function __construct(Reader $reader, RouterInterface $router, ResourceInterface $resource)
    {
        $this->reader = $reader;
        $this->router = $router;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $url = parse_url($ro->uri);
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

        return $locatedResource->toString();
    }

    /**
     * @return mixed
     */
    private function getReverseMatchedLink(string $uri)
    {
        $urlParts = parse_url($uri);
        $routeName = $urlParts['path'];
        isset($urlParts['query']) ? parse_str($urlParts['query'], $value) : $value = [];
        if ($value === []) {
            return $uri;
        }
        $reverseUri = $this->router->generate($routeName, (array) $value);
        if (is_string($reverseUri)) {
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
