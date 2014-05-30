<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Cache\Interceptor;

trait EtagTrait
{
    /**
     * Return etag
     *
     * @param $object
     * @param $args
     *
     * @return int
     */
    public function getEtag($object, $args)
    {
        $args = ($args instanceof \ArrayObject) ? $args->getArrayCopy() : $args;
        $etag = crc32(get_class($object) . serialize($args));
        error_log(sprintf(
            'etag:%s class:%s args:$s tag:%s',
            $etag,
            get_class($object),
            serialize($args)
        ));

        return $etag;
    }
}
