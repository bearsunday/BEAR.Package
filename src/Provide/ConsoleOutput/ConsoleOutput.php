<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ConsoleOutput;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Request;
use BEAR\Sunday\Extension\ConsoleOutput\ConsoleOutputInterface;

final class ConsoleOutput implements ConsoleOutputInterface
{

    const LABEL = "\033[1;32m";
    const LABEL1 = "\033[1;33m";
    const LABEL2 = "\e[4;30m";
    const CLOSE = "\033[0m";

    /**
     * @var bool
     */
    private $enableOutput = true;

    /**
     * @return $this
     */
    public function disableOutput()
    {
        $this->enableOutput = false;

        return $this;
    }

    /**
     * Send CLI output
     *
     * @param ResourceObject $resource
     * @param string         $statusText
     *
     * @return string
     */
    public function send(
        ResourceObject $resource,
        $statusText = ''
    ) {
        // code
        $output = self::LABEL . $resource->code . ' ' . $statusText . self::CLOSE . PHP_EOL;
        // resource headers
        $header = $this->getHeader($resource);
        // body
        $output .= $header;
        $output .= self::LABEL . '[BODY]' . self::CLOSE . PHP_EOL;
        if (is_scalar($resource->body)) {
            $output .= (string) $resource->body;
            goto COMPLETE;
        }
        $isTraversable = is_array($resource->body) || $resource->body instanceof \Traversable;
        $body = $isTraversable ? $this->getBody($resource) : '*'. gettype($resource->body);
        $output .= $body;
        if ($resource->view) {
            $output .= PHP_EOL . self::LABEL . '[VIEW]' . self::CLOSE . PHP_EOL . $resource->view;
        }

        COMPLETE:
        if ($this->enableOutput) {
            // @codeCoverageIgnoreStart
            echo $output . PHP_EOL;
        }
        // @codeCoverageIgnoreEnd

        return $output;

    }

    /**
     * @param ResourceObject $resource
     *
     * @return string
     */
    private function getHeader(ResourceObject $resource)
    {
        $header = '';
        foreach ($resource->headers as $name => $value) {
            $value = (is_array($value)) ? json_encode($value, true) : $value;
            $header .= self::LABEL1 . "{$name}: " . self::CLOSE . "{$value}" . PHP_EOL;
        }
        return $header;
    }

    /**
     * @param ResourceObject $resource
     *
     * @return string
     */
    private function getBody(ResourceObject $resource)
    {
        return $this->getVarDump($resource->body);
    }

    /**
     * @param mixed   $target
     * @param integer $level
     * @param string  $header
     * @param string  $footer
     *
     * @return string
     */
    private function getVarDump($target, $level = 0, $header = '', $footer = '')
    {
        $string = '';

        if (is_array($target)) {
            $string .= $header;

            foreach ($target as $key => $body) {
                $string .=  str_repeat('  ', $level) .
                    self::LABEL1 . $key . self::CLOSE . ' ' .
                    $this->getVarDump(
                        $body,
                        $level + 1,
                        '=> array(' . PHP_EOL,
                        str_repeat('  ', $level) . ')'
                    ) .
                    ',' . PHP_EOL;
            }
            $string .= $footer;

            return $string;
        }
        if (is_object($target) && $target instanceof Request) {
            $string .= $this->getRequestString($target);

            return $string;
        }

        if (is_object($target)) {
            $string .= $this->getVarDump(
                get_object_vars($target),
                $level,
                '=> ' . get_class($target) . '(' . PHP_EOL,
                str_repeat('  ', $level - 1) . ')'
            );

            return $string;
        }

        $string .= $target;
        return $string;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getRequestString(Request $request)
    {
        $body = self::LABEL2 . $request->toUri() . self::CLOSE;
        return $body;
    }
}
