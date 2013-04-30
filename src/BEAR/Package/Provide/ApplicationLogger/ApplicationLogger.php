<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger;

use BEAR\Resource\Logger as ResourceLogger;
use BEAR\Resource\LoggerInterface as ResourceLoggerInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface;
use Ray\Di\Di\Inject;

/**
 * Logger
 */
final class ApplicationLogger implements ApplicationLoggerInterface
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
    public function register(AppInterface $app)
    {
        register_shutdown_function(
            // @codeCoverageIgnoreStart
            function () {
                $this->logger->write();
            }
            // @codeCoverageIgnoreEnd
        );
    }
}
