<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Logger;

use BEAR\AppMeta\AbstractAppMeta;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Ray\Di\ProviderInterface;

class MonologProviver implements ProviderInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    /**
     * @param AbstractAppMeta $appMeta
     */
    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    public function get()
    {
        return new Logger($this->appMeta->name, [new ErrorLogHandler]);
    }
}
