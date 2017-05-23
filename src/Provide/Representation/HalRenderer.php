<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Annotation\Curies;
use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\Package\Exception\LocationHeaderRequestException;
use BEAR\Resource\AbstractRequest;
use BEAR\Resource\AbstractUri;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceInterface;
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
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @var Curies
     *
     */
    private $curies;

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
        $method = 'on' . ucfirst($ro->uri->method);
        $hasMethod = method_exists($ro, $method);
        if (! $hasMethod) {
            $ro->view = ''; // OPTIONS request no view

            return '';
        }
        $annotations = ($hasMethod) ? $this->reader->getMethodAnnotations(new \ReflectionMethod($ro, $method)) : [];
        $this->curies = $this->reader->getClassAnnotation(new \ReflectionClass($ro), Curies::class);
        $isReturnCreatedResource = $ro->code === 201 && isset($ro->headers['Location']) && $ro->uri->method === 'post' && $this->hasReturnCreatedResourceAnnotation($annotations);
        if ($isReturnCreatedResource) {
            $ro->view = $this->getLocatedView($ro);

            return $ro->view;
        }
        list($ro, $body) = $this->valuate($ro);
        /* @var $annotations Link[] */
        /* @var $ro ResourceObject */
        $hal = $this->getHal($ro->uri, $body, $annotations);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $this->updateHeaders($ro);

        return $ro->view;
    }

    /**
     * @return bool
     */
    private function hasReturnCreatedResourceAnnotation(array $annotations)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ReturnCreatedResource) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \BEAR\Resource\ResourceObject $ro
     */
    private function valuateElements(ResourceObject &$ro)
    {
        foreach ($ro->body as $key => &$embeded) {
            if ($embeded instanceof AbstractRequest) {
                $isDefferentSchema = $this->isDifferentSchema($ro, $embeded->resourceObject);
                if ($isDefferentSchema === true) {
                    $ro->body['_embedded'][$key] = $embeded()->body;
                    unset($ro->body[$key]);
                    continue;
                }
                unset($ro->body[$key]);
                $view = $this->render($embeded());
                $ro->body['_embedded'][$key] = json_decode($view);
            }
        }
    }

    /**
     * Return is different schema (page <-> app)
     *
     * @param ResourceObject $parentRo
     * @param ResourceObject $childRo
     *
     * @return bool
     */
    private function isDifferentSchema(ResourceObject $parentRo, ResourceObject $childRo)
    {
        return $parentRo->uri->host . $parentRo->uri->host !== $childRo->uri->scheme . $childRo->uri->host;
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
     * @param array $methodAnnotations
     * @param Hal   $hal
     *
     * @internal param Uri $uri
     */
    private function getHalLink(array $body, array $methodAnnotations, Hal $hal)
    {
        if ($this->curies instanceof Curies) {
            $hal->addCurie($this->curies->name, $this->curies->href);
        }
        foreach ($methodAnnotations as $annotation) {
            if (! $annotation instanceof Link) {
                continue;
            }
            $uri = uri_template($annotation->href, $body);
            $reverseUri = $this->getReverseMatchedLink($uri);
            $hal->addLink($annotation->rel, $reverseUri);
        }
        if (isset($body['_links'])) {
            foreach ($body['_links'] as $rel => $annotation) {
                $hal->addLink($rel, $annotation);
            }
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

    /**
     * Return `Location` URI view
     *
     * @return string
     */
    private function getLocatedView(ResourceObject $ro)
    {
        $url = parse_url($ro->uri);
        $locationUri = sprintf('%s://%s%s', $url['scheme'], $url['host'], $ro->headers['Location']);
        try {
            $locatedResource = $this->resource->uri($locationUri)->eager->request();
        } catch (\Exception $e) {
            throw new LocationHeaderRequestException($locationUri, 0, $e);
        }

        return $locatedResource->toString();
    }
}
