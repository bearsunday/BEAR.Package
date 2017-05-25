<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Doctrine\Common\Annotations\Reader;
use Nocarrier\Hal;

class NoContentRenderer implements RenderInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        return '';
    }
}
