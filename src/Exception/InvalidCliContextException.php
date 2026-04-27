<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/** Thrown when the CLI context receives server data without CLI arguments. */
final class InvalidCliContextException extends InvalidContextException
{
}
