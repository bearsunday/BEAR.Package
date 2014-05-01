<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog;

use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Collection;
use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Fire;
use BEAR\Sunday\Inject\LogDirInject;
use Ray\Di\ProviderInterface;
use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Zf2Log;
use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Zf2LogProvider;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Writer provider
 */
class DevWritersProvider implements ProviderInterface
{

    use LogDirInject;

    /**
     * @return Collection
     */
    public function get()
    {
        $writers = new Collection(
            [
                new Fire,
                new Zf2Log(new Zf2LogProvider($this->logDir))
            ]
        );

        return $writers;
    }
}
