<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Types;

use function is_string;

/** @psalm-import-type QueryParams from Types */
final class QueryParamNormalizer
{
    /**
     * Keep only string-keyed entries
     *
     * parse_str() and json_decode() may yield int keys (e.g. "0=foo"), which are not valid query parameters.
     *
     * @param array<int|string, mixed> $params
     *
     * @return QueryParams
     *
     * @psalm-suppress MixedAssignment
     */
    public static function normalize(array $params): array
    {
        $query = [];
        foreach ($params as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $query[$key] = $value;
        }

        return $query;
    }
}
