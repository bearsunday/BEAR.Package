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
    /**
     * @param $externalUri
     *
     * @return string
     */
    public function map($externalUri)
    {

        $firstSlashPos = strpos($pagePath, '/');
        $uri = sprintf(
            "%s://%s",
            substr($pagePath, 0, $firstSlashPos),
            substr($pagePath, $firstSlashPos)
        );

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseMap($externalBaseUri, $internalUri)
    {
        $parsedUrl = parse_url($internalUri);
        $uri  = $externalBaseUri . "/{$parsedUrl['scheme']}{$parsedUrl['path']}/";
        if (isset($parsedUrl['query'])) {
            $uri .= '?' . $parsedUrl['query'];
        }

        return $uri;
    }
}
