<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Transfer;

use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Transfer\TransferInterface;

class CliResponder implements TransferInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ResourceObject $ro, array $server)
    {
        unset($server);
        if (! $ro->view) {
            $ro->toString();
        }
        // code
        $statusText = (new Code)->statusText[$ro->code];
        $ob = $ro->code . ' ' . $statusText . PHP_EOL;
        // header
        foreach ($ro->headers as $label => $value) {
            $ob .= "{$label}: {$value}" . PHP_EOL;
        }
        // empty line
        $ob .= PHP_EOL;
        // body
        $ob .= (string) $ro->view;
        echo $ob;
    }
}
