<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\AbstractUri;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Doctrine\Common\Annotations\Reader;
use Nocarrier\Hal;

/**
 * HAL(Hypertext Application Language) renderer
 */
class HalRenderer implements RenderInterface
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
     * @param Reader          $reader
     * @param RouterInterface $router
     */
    public function __construct(Reader $reader, RouterInterface $router)
    {
        $this->reader = $reader;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        list($ro, $body) = $this->valuate($ro);
        $method = 'on' . ucfirst($ro->uri->method);
        $hasMethod = method_exists($ro, $method);
        if (! $hasMethod) {
            $ro->view = ''; // OPTIONS request no view

            return '';
        }
        $annotations = ($hasMethod) ? $this->reader->getMethodAnnotations(new \ReflectionMethod($ro, $method)) : [];
        /* @var $annotations Link[] */
        /* @var $ro ResourceObject */
        $hal = $this->getHal($ro->uri, $body, $annotations);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $this->updateHeaders($ro);

        return $ro->view;
    }

    /**
     * @param \BEAR\Resource\ResourceObject $ro
     */
    private function valuateElements(ResourceObject &$ro)
    {
        foreach ($ro->body as $key => &$element) {
            if ($element instanceof RequestInterface) {
                unset($ro->body[$key]);
                $view = $this->render($element());
                $ro->body['_embedded'][$key] = json_decode($view);
            }
        }
    }

    /**
     * @param Uri   $uri
     * @param array $body
     * @param array $annotations
     *
     * @return Hal
     */
    private function getHal(AbstractUri $uri, array $body, array $annotations)
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->getReverseMatchedLink($path);
        $hal = new Hal($selfLink, $body);
        $this->getHalLink($body, $annotations, $hal);

        return $hal;
    }

    /**
     * @param string $uri
     *
     * @return mixed
     */
    private function getReverseMatchedLink($uri)
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

    /**
     * @param ResourceObject $ro
     *
     * @return array [ResourceObject, array]
     */
    private function valuate(ResourceObject $ro)
    {
        // evaluate all request in body.
        if (is_array($ro->body)) {
            $this->valuateElements($ro);
        }
        // HAL
        $body = $ro->body ?: [];
        if (is_scalar($body)) {
            $body = ['value' => $body];

            return [$ro, $body];
        }

        return[$ro, (array) $body];
    }

    /**
     * @param array $body
     * @param array $links
     * @param Hal   $hal
     *
     * @internal param Uri $uri
     */
    private function getHalLink(array $body, array $links, Hal $hal)
    {
        foreach ($links as $link) {
            if (! $link instanceof Link) {
                continue;
            }
            $uri = uri_template($link->href, $body);
            $reverseUri = $this->getReverseMatchedLink($uri);
            $hal->addLink($link->rel, $reverseUri);
        }
    }

    /**
     * @param ResourceObject $ro
     */
    private function updateHeaders(ResourceObject $ro)
    {
        $ro->headers['content-type'] = 'application/hal+json';
        if (isset($ro->headers['Location'])) {
            $ro->headers['Location'] = $this->getReverseMatchedLink($ro->headers['Location']);
        }
    }
}
