<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch;

final class ProdVndErrorPageFactory implements ErrorPageFactoryInterface
{
    public function newInstance(\Exception $e, RouterMatch $request) : ProdVndErrorPage
    {
        return new ProdVndErrorPage($e, $request);
    }
}
