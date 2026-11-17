<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Exception\LocationHeaderRequestException;
use BEAR\Package\Provide\Router\QueryParamNormalizer;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Override;
use Throwable;

use function is_string;
use function parse_str;
use function parse_url;
use function sprintf;
use function strpos;
use function substr;

use const PHP_URL_HOST;
use const PHP_URL_PATH;
use const PHP_URL_QUERY;
use const PHP_URL_SCHEME;

/**
 * 201 CreatedResource renderer
 */
final class CreatedResourceRenderer implements RenderInterface
{
    private const string FRAGMENT_SEPARATOR = '#';

    public function __construct(
        private RouterInterface $router,
        private ResourceInterface $resource,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function render(ResourceObject $ro)
    {
        $urlSchema = (string) parse_url((string) $ro->uri, PHP_URL_SCHEME);
        $urlHost = (string) parse_url((string) $ro->uri, PHP_URL_HOST);
        [$location, $fragment] = $this->splitFragment($ro->headers['Location']);
        $locationUri = sprintf('%s://%s%s', $urlSchema, $urlHost, $location);
        try {
            $locatedResource = $this->resource->uri($locationUri)();
        } catch (Throwable $e) {
            $ro->code = 500;
            $ro->view = '';

            throw new LocationHeaderRequestException($locationUri, 0, $e);
        }

        $this->updateHeaders($ro, $location, $fragment);
        $ro->view = $locatedResource->toString();

        return $ro->view;
    }

    private function getReverseMatchedLink(string $uri): string
    {
        $routeName = (string) parse_url($uri, PHP_URL_PATH);
        $urlQuery = (string) parse_url($uri, PHP_URL_QUERY);
        $urlQuery ? parse_str($urlQuery, $value) : $value = [];
        $value = QueryParamNormalizer::normalize($value);
        if ($value === []) {
            return $uri;
        }

        $reverseUri = $this->router->generate($routeName, $value);
        if (is_string($reverseUri)) {
            return $reverseUri;
        }

        return $uri;
    }

    /** @return array{string, string} the URI without its fragment, and the fragment with its separator ('' if none) */
    private function splitFragment(string $uri): array
    {
        $separatorPosition = strpos($uri, self::FRAGMENT_SEPARATOR);
        if ($separatorPosition === false) {
            return [$uri, ''];
        }

        return [substr($uri, 0, $separatorPosition), substr($uri, $separatorPosition)];
    }

    private function updateHeaders(ResourceObject $ro, string $location, string $fragment): void
    {
        $ro->headers['content-type'] = 'application/hal+json';
        $ro->headers['Location'] = $this->getReverseMatchedLink($location) . $fragment;
    }
}
