<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\Annotation\Link;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Nocarrier\Hal;

final class HalLink
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getReverseLink($uri) : string
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

    public function addHalLink(array $body, array $methodAnnotations, Hal $hal) : Hal
    {
        if (! empty($methodAnnotations)) {
            $hal = $this->linkAnnotation($body, $methodAnnotations, $hal);
        }
        if (isset($body['_links'])) {
            $hal = $this->bodyLink($body, $hal);
        }

        return $hal;
    }

    private function linkAnnotation(array $body, array $methodAnnotations, Hal $hal) : Hal
    {
        foreach ($methodAnnotations as $annotation) {
            if (! $annotation instanceof Link) {
                continue;
            }
            $uri = uri_template($annotation->href, $body);
            $reverseUri = $this->getReverseLink($uri);
            $hal->addLink($annotation->rel, $reverseUri);
        }

        return $hal;
    }

    private function bodyLink(array $body, Hal $hal) : Hal
    {
        foreach ((array) $body['_links'] as $rel => $link) {
            $attr = $link;
            unset($attr['href']);
            $hal->addLink($rel, $link['href'], $attr);
        }

        return $hal;
    }
}
