<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module;

use BEAR\Package\Provide\Router\CliRouter;
use BEAR\Package\Provide\Transfer\CliResponder;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Ray\Di\AbstractModule;

class CliModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->rename(RouterInterface::class, 'original');
        $this->bind(RouterInterface::class)->to(CliRouter::class);
        $this->bind(TransferInterface::class)->to(CliResponder::class);
    }
}
