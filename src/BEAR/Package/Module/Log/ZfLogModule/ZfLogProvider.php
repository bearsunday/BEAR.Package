<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Log\ZfLogModule;

use BEAR\Sunday\Inject\LogDirInject;
use Guzzle\Log\Zf2LogAdapter;
use Ray\Di\ProviderInterface as Provide;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Zend log provider
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ZfLogProvider implements Provide
{
    use LogDirInject;

    /**
     * Provide instance
     *
     * @return \Guzzle\Log\LogAdapterInterface
     */
    public function get()
    {
        $logger = new Logger;
        $writer = new Stream($this->logDir . '/app.log');
        $logger->addWriter($writer);

        return new Zf2LogAdapter($logger);
    }
}
