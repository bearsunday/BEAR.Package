<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Provide\ResourceView;

interface UriConverterInterface
{
    /**
     * @param string $externalBaseUri
     * @param string $internalUri
     *
     * @return string
     */
    public function convert($externalBaseUri, $internalUri);
}
