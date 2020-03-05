<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Transfer;

use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use BEAR\Sunday\Provide\Transfer\ConditionalResponseInterface;
use BEAR\Sunday\Provide\Transfer\HeaderInterface;
use BEAR\Sunday\Provide\Transfer\Output;
use const PHP_EOL;

final class CliResponder implements TransferInterface
{
    /**
     * @var HeaderInterface
     */
    private $header;

    /**
     * @var ConditionalResponseInterface
     */
    private $condResponse;

    public function __construct(HeaderInterface $header, ConditionalResponseInterface $condResponse)
    {
        $this->header = $header;
        $this->condResponse = $condResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ResourceObject $ro, array $server) : void
    {
        $isModified = $this->condResponse->isModified($ro, $server);
        $output = $isModified ? $this->getOutput($ro, $server) : $this->condResponse->getOutput($ro->headers);

        $statusText = (new Code)->statusText[$ro->code] ?? '';
        $ob = $output->code . ' ' . $statusText . PHP_EOL;

        // header
        foreach ($output->headers as $label => $value) {
            $ob .= "{$label}: {$value}" . PHP_EOL;
        }

        // empty line
        $ob .= PHP_EOL;

        // body
        $ob .= (string) $output->view;

        echo $ob;
    }

    private function getOutput(ResourceObject $ro, array $server) : Output
    {
        $ro->toString(); // set headers as well
        return new Output($ro->code, ($this->header)($ro, $server), $ro->view ?: $ro->toString());
    }
}
