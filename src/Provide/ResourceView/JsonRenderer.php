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

class JsonRenderer implements RenderInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        // evaluate all request in body.
        /** @noinspection PhpUndefinedFieldInspection */
        if (is_array($ro->body) || $ro->body instanceof \Traversable) {
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
        $ro->view = json_encode($ro->body, JSON_PRETTY_PRINT);

        return $ro->view;
    }
}
