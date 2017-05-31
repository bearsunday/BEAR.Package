<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch;

final class ProdVndErrorPageFactory implements ErrorPageFactoryInterface
{
    /**
     * @param \Exception  $e
     * @param RouterMatch $request
     *
     * @return ProdVndErrorPage
     */
    public function newInstance(\Exception $e, RouterMatch $request)
    {
        return new ProdVndErrorPage($e, $request);
    }
}
