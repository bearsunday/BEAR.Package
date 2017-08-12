<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Router\RouterMatch;

final class LogRef
{
    /**
     * @var string
     */
    private $ref;

    /**
     * @var ExceptionAsString
     */
    private $exceptionString;

    public function __construct(\Exception $e)
    {
        $this->ref = (string) hash('crc32b', get_class($e) . $e->getMessage() . $e->getFile() . $e->getLine());
        $this->exceptionString = new ExceptionAsString;
    }

    public function __toString()
    {
        return $this->ref;
    }

    public function log(\Exception $e, RouterMatch $request, AbstractAppMeta $appMeta)
    {
        $logRefFile = sprintf('%s/logref.%s.log', $appMeta->logDir, $this->ref);
        $log = $this->exceptionString->detail($e, $request);
        @file_put_contents($logRefFile, $log);
        $linkFile = sprintf('%s/last.logref.log', $appMeta->logDir);
        is_link($linkFile) && is_writable($linkFile) && @unlink($linkFile);
        @symlink($logRefFile, $linkFile);
    }
}
