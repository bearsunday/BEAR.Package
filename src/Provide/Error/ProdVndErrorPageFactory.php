<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch;
use Throwable;

final class ProdVndErrorPageFactory implements ErrorPageFactoryInterface
{
    public function newInstance(Throwable $e, RouterMatch $request): ProdVndErrorPage
    {
        return new ProdVndErrorPage($e, $request);
    }
}
