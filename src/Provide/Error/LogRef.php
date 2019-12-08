<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Router\RouterMatch;

final class LogRef
{
    /**
     * @var string
     */
    private $ref;

    public function __construct(\Exception $e)
    {
        $this->ref = hash('crc32b', \get_class($e) . $e->getMessage() . $e->getFile() . $e->getLine());
    }

    public function __toString()
    {
        return $this->ref;
    }

    public function log(\Exception $e, RouterMatch $request, AbstractAppMeta $appMeta) : void
    {
        $logRefFile = sprintf('%s/logref.%s.log', $appMeta->logDir, $this->ref);
        $log = (string) new ExceptionAsString($e, $request);
        @file_put_contents($logRefFile, $log);
        $linkFile = sprintf('%s/last.logref.log', $appMeta->logDir);
        is_link($linkFile) && is_writable($linkFile) && @unlink($linkFile);
        @symlink($logRefFile, $linkFile);
    }
}
