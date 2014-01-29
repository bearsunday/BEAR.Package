<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

class SchemeFirstPathUriConverter implements UriConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($externalBaseUri, $internalUri)
    {
        $parsedUrl = parse_url($internalUri);
        $uri  = $externalBaseUri . "/{$parsedUrl['scheme']}{$parsedUrl['path']}/";
        if (isset($parsedUrl['query'])) {
            $uri .= '?' . $parsedUrl['query'];
        }

        return $uri;
    }
}
