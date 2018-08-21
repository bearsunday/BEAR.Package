<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch;

final class ProdVndErrorPageFactory implements ErrorPageFactoryInterface
{
    public function newInstance(\Exception $e, RouterMatch $request) : ProdVndErrorPage
    {
        return new ProdVndErrorPage($e, $request);
    }
}
