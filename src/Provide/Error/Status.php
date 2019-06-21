<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\Exception\BadRequestException;
use Koriym\HttpConstants\StatusCode;

final class Status
{
    /**
     * @var int
     */
    public $code;

    /**
     * @var string
     */
    public $text;

    public function __construct(\Exception $e)
    {
        $text = (new StatusCode)->statusText;
        if ($e instanceof BadRequestException) {
            $this->code = (int) $e->getCode();
            $this->text = $text[$this->code] ?? '';

            return;
        }
        if ($e instanceof \RuntimeException) {
            $this->code = 503;
            $this->text = $text[$this->code];

            return;
        }
        $this->code = 500;
        $this->text = $text[$this->code];
    }
}
