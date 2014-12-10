<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Transfer;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Transfer\TransferInterface;

class CliResponder implements TransferInterface
{
    public function __invoke(ResourceObject $resourceObject)
    {
        // code
        $ob = 'code: ' . $resourceObject->code . PHP_EOL;

        // header
        $ob .= 'header:' . PHP_EOL;
        foreach ($resourceObject->headers as $label => $value) {
            $ob .= "{$label}: {$value}";
        }

        // body
        $ob .= 'body:' . PHP_EOL;
        $ob .= (string) $resourceObject;

        echo $ob . PHP_EOL;
    }
}
