<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use BEAR\Resource\ResourceObject;
use Nocarrier\Hal;
use BEAR\Resource\Link;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class HalFactory implements HalFactoryInterface
{
    /**
     * @var \BEAR\Resource\Uri
     */
    protected $uri;

    /**
     * @var SchemeUriMapper
     */
    protected $converter;

    /**
     * @param UriMapperInterface $converter
     *
     * @Inject
     */
    public function __construct(UriMapperInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\HrefNotFound
     */
    public function get(ResourceObject $ro, $data)
    {
        $baseUri = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'http://localhost';
        $selfUri = $this->converter->reverseMap($baseUri, $ro->uri);
        $hal = new Hal($selfUri, $data);
        foreach ($ro->links as $rel => $link) {
            $title = (isset($link[Link::TITLE])) ? $link[Link::TITLE] : null;
            $attr = (isset($link[Link::TEMPLATED]) && $link[Link::TEMPLATED] === true) ? [Link::TEMPLATED => true] : [];

            if (isset($link[Link::HREF])) {
                $uri = $this->converter->reverseMap($baseUri, $link[Link::HREF]);
                $hal->addLink($rel, $uri, $attr);

                continue;
            }
            throw new Exception\HrefNotFound($rel);
        }

        return $hal;
    }
}
