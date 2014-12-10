<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\Reader;
use Nocarrier\Hal;
use Ray\Aop\WeavedInterface;

/**
 * HAL(Hypertext Application Language) renderer
 */
class HalRenderer implements RenderInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        // evaluate all request in body.
        if (is_array($ro->body)) {
            $this->valuateElements($ro);
        }
        // HAL
        $body = $ro->body ? : [];
        if (is_scalar($body)) {
            $body = ['value' => $body];
        }
        $class = ($ro instanceof WeavedInterface) ? (new \ReflectionClass($ro))->getParentClass()->name : $ro;
        $links = $this->reader->getMethodAnnotations(new \ReflectionMethod($class, 'onGet'), Link::class);
        /** @var $links Link[] */
        $hal = $this->getHal($ro->uri, $body, $links);
        $ro->view = $hal->asJson(true) . PHP_EOL;

        return $ro->view;
    }

    /**
     * @param \BEAR\Resource\ResourceObject $ro
     */
    private function valuateElements(ResourceObject &$ro)
    {
        array_walk_recursive(
            $ro->body,
            function (&$element) {
                if ($element instanceof RequestInterface) {
                    /** @var $element callable */
                    $element = $element();
                }
            }
        );
    }

    /**
     * @param array  $body
     * @param Link[] $links
     *
     * @return Hal
     */
    private function getHal(Uri $uri, array $body, array $links)
    {
        $query = $uri->query ? '?' . http_build_query($uri->query) : '';
        $path = $uri->path . $query;
        $hal = new Hal($path, $body);
        foreach ($links as $link) {
            if (! $link instanceof Link) {
                continue;
            }
            $uri = uri_template($link->href, $body);
            $parsed = parse_url($uri);
            $uri = $parsed['path'] . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
            $hal->addLink($link->rel, $uri);
        }

        return $hal;

    }
}
