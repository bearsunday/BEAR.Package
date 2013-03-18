<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ConsoleOutput;

use BEAR\Sunday\Extension\ConsoleOutput\ConsoleOutputInterface;
use BEAR\Resource\AbstractObject as ResourceObject;
use Guzzle\Parser\UriTemplate\UriTemplate;

/**
 * Cli Output
 *
 * @package    BEAR.Package
 * @subpackage Web
 */
final class ConsoleOutput implements ConsoleOutputInterface
{
    const MODE_REQUEST = 'request';
    const MODE_VIEW = 'view';
    const MODE_VALUE = 'value';

    const LABEL = "\033[1;32m";
    const LABEL1 = "\033[1;33m";
    const LABEL2 = "\e[4;30m";
    const CLOSE = "\033[0m";

    /**
     * Send CLI output
     *
     * @param ResourceObject $resource
     * @param string         $statusText
     * @param string         $mode
     */
    public function send(
        ResourceObject $resource,
        $statusText = '',
        $mode = self::MODE_VIEW
    ) {
        // code
        $codeMsg = self::LABEL . $resource->code . ' ' . $statusText . self::CLOSE . PHP_EOL;
        echo $codeMsg;
        // resource headers
        $header = $this->getHeader($resource);
        // body
        echo $header;
        echo self::LABEL . '[BODY]' . self::CLOSE . PHP_EOL;
        if ($resource->view) {
            echo $resource->view;
            goto complete;
        }
        $isTraversable = is_array($resource->body) || $resource->body instanceof \Traversable;
        if (!$isTraversable) {
            $resource->body;
            goto complete;
        }
        $body = $this->getBody($resource, $mode);
        echo $body;

        // @codingStandardsIgnoreStart
        complete:
        // @codingStandardsIgnoreEnd

        echo PHP_EOL;
    }

    private function getHeader(ResourceObject $resource)
    {
        $header = '';
        foreach ($resource->headers as $name => $value) {
            $value = (is_array($value)) ? json_encode($value, true) : $value;
            $header = self::LABEL1 . "{$name}: " . self::CLOSE . "{$value}" . PHP_EOL;
        }

        return $header;
    }

    private function getBody(ResourceObject $resource, $mode)
    {
        foreach ($resource->body as $key => $body) {
            if ($body instanceof \BEAR\Resource\Request) {
                switch ($mode) {
                    case self::MODE_REQUEST:
                        $body = self::LABEL2 . $body->toUri() . self::CLOSE;
                        break;
                    case self::MODE_VALUE:
                        $value = $body();
                        $body = var_export($value, true) . self::LABEL2 . $body->toUri() . self::CLOSE;
                        break;
                    case self::MODE_VIEW:
                    default:
                        $body = (string)$body . ' ' . self::LABEL1 . $body->toUri() . self::CLOSE;
                        break;

                }
            }
            $body = is_array($body) ? var_export($body, true) : $body;
            $body =  self::LABEL1 . $key . self::CLOSE . $body . PHP_EOL;

            return $body;
        }
    }
}
