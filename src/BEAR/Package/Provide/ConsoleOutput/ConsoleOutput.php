<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ConsoleOutput;

use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Request;
use BEAR\Sunday\Extension\ConsoleOutput\ConsoleOutputInterface;
use Guzzle\Parser\UriTemplate\UriTemplate;

/**
 * Cli Output
 *
 * @package    BEAR.Package
 * @subpackage Web
 */
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
     * @return self
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
        $codeMsg = self::LABEL . $resource->code . ' ' . $statusText . self::CLOSE . PHP_EOL;
        $output = $codeMsg;
        // resource headers
        $header = $this->getHeader($resource);
        // body
        $output .= $header;
        $output .= self::LABEL . '[BODY]' . self::CLOSE . PHP_EOL;
        if (is_scalar($resource->body)) {
            $output .= (string) $resource->body;
            goto complete;
        }
        $isTraversable = is_array($resource->body) || $resource->body instanceof \Traversable;
        if ($isTraversable) {
            $body = $this->getBody($resource);
        } else {
            $body = '*'. gettype($resource->body);
        }
        $output .= $body;
        if ($resource->view) {
            $output .= self::LABEL . '[VIEW]' . self::CLOSE . PHP_EOL . $resource->view;
        }

        complete:
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
            $header = self::LABEL1 . "{$name}: " . self::CLOSE . "{$value}" . PHP_EOL;
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
        $string = '';
        foreach ($resource->body as $key => &$body) {
            if ($body instanceof Request) {
                $body = $this->getRequestString($body);
            }
            if (is_array($body)) {
                $body = str_replace(["\n", " "], '', var_export($body, true));
            }
            $string .= self::LABEL1 . $key . self::CLOSE . " {$body}" . PHP_EOL;
        }
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
