<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterMatch;

interface ErrorPageFactoryInterface
{
    /**
     * @return ResourceObject
     */
    public function newInstance(\Exception $e, RouterMatch $request);
}
