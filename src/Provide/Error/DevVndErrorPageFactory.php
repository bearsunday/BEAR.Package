<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch;

final class DevVndErrorPageFactory implements ErrorPageFactoryInterface
{
    public function newInstance(\Exception $e, RouterMatch $request) : DevVndErrorPage
    {
        return new DevVndErrorPage($e, $request);
    }
}
