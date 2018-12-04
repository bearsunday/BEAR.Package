<?php

declare(strict_types=1);

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
