<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Transfer;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Provide\Transfer\HttpResponder;

class FakeHttpResponder extends HttpResponder
{
    public static $code = [];
    public static $headers = [];
    public static $content;

    public function __invoke(ResourceObject $ro, array $server)
    {
        unset($server);
        $ro->toString();
        self::$code = $ro->code;
        self::$headers = $ro->headers;
        self::$content = $ro->view;
    }

    public static function reset()
    {
        static::$headers = [];
        static::$content = null;
    }
}
