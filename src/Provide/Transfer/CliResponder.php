<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
        $body = (string) $resourceObject;
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
