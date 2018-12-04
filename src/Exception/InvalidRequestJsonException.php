<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use BEAR\Resource\Exception\BadRequestException;

class InvalidRequestJsonException extends BadRequestException implements ExceptionInterface
{
}
