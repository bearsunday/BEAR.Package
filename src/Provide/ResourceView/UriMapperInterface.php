<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Provide\ResourceView;

interface UriMapperInterface
{
    /**
     * @param $requestUri "/blog/posts"
     *
     * @return string
     */
    public function map($requestUri);

    /**
     * @param string $httpHost
     * @param string $internalUri
     *
     * @return string
     */
    public function reverseMap($httpHost, $internalUri);
}
