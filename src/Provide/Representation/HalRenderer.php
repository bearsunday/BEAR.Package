<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Annotation\ReturnCreatedResource;
use BEAR\Package\Exception\LocationHeaderRequestException;
use BEAR\Resource\AbstractRequest;
use BEAR\Resource\AbstractUri;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
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
        if (! method_exists($ro, $method)) {
            $ro->view = ''; // no view for OPTIONS request

            return '';
        }
        $annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod($ro, $method));
        if ($this->isReturnCreatedResource($ro, $annotations)) {
            return $this->returnCreatedResource($ro);
        }

        return $this->renderHal($ro, $annotations);
    }

    private function renderHal(ResourceObject $ro, $annotations) : string
    {
        list($ro, $body) = $this->valuate($ro);
        /* @var $annotations Link[] */
        /* @var $ro ResourceObject */
        $hal = $this->getHal($ro->uri, $body, $annotations);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $this->updateHeaders($ro);

        return $ro->view;
    }

    private function isReturnCreatedResource(ResourceObject $ro, array $annotations) : bool
    {
        return $ro->code === 201 && $ro->uri->method === 'post' && isset($ro->headers['Location']) && $this->hasReturnCreatedResource($annotations);
    }

    private function hasReturnCreatedResource(array $annotations) : bool
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ReturnCreatedResource) {
                return true;
            }
        }

        return false;
    }

    private function returnCreatedResource(ResourceObject $ro) : string
    {
        $ro->view = $this->getLocatedView($ro);
        $this->updateHeaders($ro);

        return $ro->view;
    }

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
     * Return "is different schema" (page <-> app)
     */
    private function isDifferentSchema(ResourceObject $parentRo, ResourceObject $childRo) : bool
    {
        return $parentRo->uri->scheme . $parentRo->uri->host !== $childRo->uri->scheme . $childRo->uri->host;
    }

    private function getHal(AbstractUri $uri, array $body, array $annotations) : Hal
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $selfLink = $this->getReverseMatchedLink($path);

        $hal = new Hal($selfLink, $body);
        $hal = $this->getHalLink($body, $annotations, $hal);

        return $hal;
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

    /**
     * @return array [ResourceObject, array]
     */
    private function valuate(ResourceObject $ro) : array
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

    private function getHalLink(array $body, array $methodAnnotations, Hal $hal) : Hal
    {
        if (! empty($methodAnnotations)) {
            $hal = $this->linkAnnotation($body, $methodAnnotations, $hal);
        }
        if (isset($body['_links'])) {
            $hal = $this->bodyLink($body, $hal);
        }

        return $hal;
    }

    private function updateHeaders(ResourceObject $ro)
    {
        $ro->headers['content-type'] = 'application/hal+json';
        if (isset($ro->headers['Location'])) {
            $ro->headers['Location'] = $this->getReverseMatchedLink($ro->headers['Location']);
        }
    }

    /**
     * Return `Location` URI view
     */
    private function getLocatedView(ResourceObject $ro) : string
    {
        $url = parse_url($ro->uri);
        $locationUri = sprintf('%s://%s%s', $url['scheme'], $url['host'], $ro->headers['Location']);
        try {
            $locatedResource = $this->resource->uri($locationUri)();
        } catch (\Exception $e) {
            throw new LocationHeaderRequestException($locationUri, 0, $e);
        }

        return $locatedResource->toString();
    }

    private function linkAnnotation(array $body, array $methodAnnotations, Hal $hal) : Hal
    {
        foreach ($methodAnnotations as $annotation) {
            if (! $annotation instanceof Link) {
                continue;
            }
            $uri = uri_template($annotation->href, $body);
            $reverseUri = $this->getReverseMatchedLink($uri);
            $hal->addLink($annotation->rel, $reverseUri);
        }

        return $hal;
    }

    private function bodyLink(array $body, Hal $hal) : Hal
    {
        foreach ($body['_links'] as $rel => $link) {
            $attr = $link;
            unset($attr['href']);
            $hal->addLink($rel, $link['href'], $attr);
        }

        return $hal;
    }
}
