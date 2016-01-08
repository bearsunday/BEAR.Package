<?php

namespace BEAR\Package\Provide\Transfer;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Provide\Transfer\HttpResponder;

class FakeHttpResponder extends HttpResponder
{
    public static $code = [];
    public static $headers = [];
    public static $content;

    public static function reset()
    {
        static::$headers = [];
        static::$content = null;
    }

    public function __invoke(ResourceObject $resourceObject, array $server)
    {
        unset($server);
        self::$code = $resourceObject->code;
        self::$headers = $resourceObject->headers;
        self::$content = $resourceObject->view;
    }
}
