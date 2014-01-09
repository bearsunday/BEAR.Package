<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\LogWriterInterface;
use BEAR\Resource\RequestInterface;

/**
 * Log writer collection
 */
final class Collection implements LogWriterInterface
{
    /**
     * \BEAR\Resource\LogWriterInterface[]
     *
     * @var array
     */
    private $writers = [];

    /**
     * @param \BEAR\Resource\LogWriterInterface[] $writers
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
            if ($writer instanceof LogWriterInterface) {
                /** @var $writer \BEAR\Resource\LogWriterInterface */
                $writer->write($request, $result);
            }
        }
    }
}
