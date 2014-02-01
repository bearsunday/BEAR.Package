<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

class SchemeUriMapper implements UriMapperInterface
{
    public function map($requestUri)
    {
        $firstSlashPos = strpos($requestUri, '/');
        $uri = sprintf(
            "%s://self%s",
            substr($requestUri, 0, $firstSlashPos),
            substr($requestUri, $firstSlashPos)
        );

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseMap($httpHost, $internalUri)
    {
        $parsedUrl = parse_url($internalUri);
        $uri  = $httpHost . "/{$parsedUrl['scheme']}{$parsedUrl['path']}/";
        if (isset($parsedUrl['query'])) {
            $uri .= '?' . $parsedUrl['query'];
        }

        return $uri;
    }
}
