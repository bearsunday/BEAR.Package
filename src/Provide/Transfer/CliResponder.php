<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Transfer;

use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Transfer\TransferInterface;

class CliResponder implements TransferInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ResourceObject $resourceObject, array $server)
    {
        unset($server);
        $body = $resourceObject->toString();
        // code
        $statusText = (new Code)->statusText[$resourceObject->code];
        $ob = $resourceObject->code . ' ' . $statusText . PHP_EOL;
        // header
        foreach ($resourceObject->headers as $label => $value) {
            $ob .= "{$label}: {$value}" . PHP_EOL;
        }
        // empty line
        $ob .=  PHP_EOL;
        // body
        $ob .= $body;

        echo $ob . PHP_EOL;
    }
}
