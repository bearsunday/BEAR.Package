<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\Uri;
use Ray\Di\Di\Inject;

/**
 * HAL(Hypertext Application Language) renderer
 */
class HalRenderer implements RenderInterface
{
    /**
     * @var HalFactoryInterface
     */
    protected $halFactory;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @param HalFactoryInterface $halFactory
     *
     * @Inject
     */
    public function __construct(HalFactoryInterface $halFactory)
    {
        $this->halFactory = $halFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        // evaluate all request in body.
        $isArrayAccess = is_array($ro->body) || $ro->body instanceof \Traversable;
        if ($isArrayAccess) {
            $this->valuateElements($ro);
        }
        // HAL
        $data = $ro->body ? : [];
        if (is_scalar($data)) {
            $data = ['value' => $data];
        }
        $hal = $this->halFactory->get($ro, $data);
        $ro->view = $hal->asJson(true) . PHP_EOL;
        $ro->headers['content-type'] = 'application/hal+json; charset=UTF-8';

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
}
