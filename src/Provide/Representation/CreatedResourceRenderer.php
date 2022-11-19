<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Exception\LocationHeaderRequestException;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Throwable;

use function is_string;
use function parse_str;
use function parse_url;
use function sprintf;

use const PHP_URL_HOST;
use const PHP_URL_PATH;
use const PHP_URL_QUERY;
use const PHP_URL_SCHEME;

/**
 * 201 CreatedResource renderer
 */
class CreatedResourceRenderer implements RenderInterface
{
    public function __construct(
        private RouterInterface $router,
        private ResourceInterface $resource,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        $urlSchema = (string) parse_url((string) $ro->uri, PHP_URL_SCHEME);
        $urlHost = (string) parse_url((string) $ro->uri, PHP_URL_HOST);
        $locationUri = sprintf('%s://%s%s', $urlSchema, $urlHost, $ro->headers['Location']);
        try {
            $locatedResource = $this->resource->uri($locationUri)();
        } catch (Throwable $e) {
            $ro->code = 500;
            $ro->view = '';

            throw new LocationHeaderRequestException($locationUri, 0, $e);
        }

        $this->updateHeaders($ro);
        $ro->view = $locatedResource->toString();

        return $ro->view;
    }

    private function getReverseMatchedLink(string $uri): string
    {
        $routeName = (string) parse_url($uri, PHP_URL_PATH);
        $urlQuery = (string) parse_url($uri, PHP_URL_QUERY);
        $urlQuery ? parse_str($urlQuery, $value) : $value = [];
        if ($value === []) {
            return $uri;
        }

        /** @var array<string, mixed> $value */
        $reverseUri = $this->router->generate($routeName, $value);
        if (is_string($reverseUri)) {
            return $reverseUri;
        }

        return $uri;
    }

    private function updateHeaders(ResourceObject $ro): void
    {
        $ro->headers['content-type'] = 'application/hal+json';
        if (! isset($ro->headers['Location'])) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $ro->headers['Location'] = $this->getReverseMatchedLink($ro->headers['Location']);
    }
}
