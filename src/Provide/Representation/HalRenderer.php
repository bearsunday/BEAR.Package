<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\AbstractUri;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
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
     * @var HalLink
     */
    private $link;

    public function __construct(Reader $reader, HalLink $link)
    {
        $this->reader = $reader;
        $this->link = $link;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        if ($ro->view) {
            return $ro->view;
        }
        $method = 'on' . ucfirst($ro->uri->method);
        $annotations = $this->reader->getMethodAnnotations(new \ReflectionMethod((object) $ro, $method));

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

    private function valuateElements(ResourceObject $ro)
    {
        foreach ((array) $ro->body as $key => &$embedded) {
            if ($embedded instanceof AbstractRequest) {
                $isDifferentSchema = $this->isDifferentSchema($ro, $embedded->resourceObject);
                if ($isDifferentSchema === true) {
                    $ro->body['_embedded'][$key] = $embedded()->body;
                    unset($ro->body[$key]);

                    continue;
                }
                unset($ro->body[$key]);
                $view = $this->render($embedded());
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
        $selfLink = $this->link->getReverseLink($path);

        $hal = new Hal($selfLink, $body);

        return $this->link->addHalLink($body, $annotations, $hal);
    }

    /**
     * @return array [ResourceObject, array]
     */
    private function valuate(ResourceObject $ro) : array
    {
        // evaluate all request in body.
        if (\is_array($ro->body)) {
            $this->valuateElements($ro);
        }
        // HAL
        $body = $ro->body ?: [];
        if (is_scalar($body)) {
            $body = ['value' => $body];

            return [$ro, $body];
        }

        return[$ro, $body];
    }

    private function updateHeaders(ResourceObject $ro)
    {
        $ro->headers['content-type'] = 'application/hal+json';
        if (isset($ro->headers['Location'])) {
            $ro->headers['Location'] = $this->link->getReverseLink($ro->headers['Location']);
        }
    }
}
