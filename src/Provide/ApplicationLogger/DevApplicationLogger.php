<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger;

use BEAR\Resource\LoggerInterface as ResourceLoggerInterface;
use BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\PreDestroy;

final class DevApplicationLogger implements ApplicationLoggerInterface
{
    /**
     * Resource logs
     *
     * @var ResourceLoggerInterface
     */
    private $logger;

    /**
     * @param ResourceLoggerInterface $logger
     *
     * @Inject
     */
    public function __construct(ResourceLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        if ($this->logger instanceof ResourceLoggerInterface) {
            $this->logger->write();
        }
    }
}
