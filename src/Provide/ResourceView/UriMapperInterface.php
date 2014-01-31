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
     * @param $externalUri
     *
     * @return string
     */
    public function map($externalUri);

    /**
     * @param string $externalBaseUri
     * @param string $internalUri
     *
     * @return string
     */
    public function reverseMap($externalBaseUri, $internalUri);
}
