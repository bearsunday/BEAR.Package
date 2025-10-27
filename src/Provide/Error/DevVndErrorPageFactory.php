<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch;
use Override;
use Throwable;

final class DevVndErrorPageFactory implements ErrorPageFactoryInterface
{
    #[Override]
    public function newInstance(Throwable $e, RouterMatch $request): DevVndErrorPage
    {
        return new DevVndErrorPage($e, $request);
    }
}
