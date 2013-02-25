<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\LogWriterInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\AbstractObject as ResourceObject;

/**
 * Writer collection
 */
final class Collection implements LogWriterInterface
{
    /**
     * @var array \BEAR\Resource\LogWriterInterface[]
     */
    private $writers = [];

    /**
     * @param array $loggers
     *
     * @Inject
     * @Named("log_writers")
     */
    public function __construct(array $writers)
    {
        $this->writers = $writers;
    }

    /**
     * {@inheritdoc}
     */
    public function write(RequestInterface $request, ResourceObject $result)
    {
        foreach ($this->writers as $writer) {
            /** @var $writer \BEAR\Resource\LogWriterInterface */
            $writer->write($request, $result);
        }
    }
}
