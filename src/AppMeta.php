<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

final class AppMeta extends AbstractAppMeta
{
    public function __construct($name)
    {
        $this->name = $name;
        $this->appDir = dirname(dirname(dirname((new \ReflectionClass($name . '\Module\App'))->getFileName())));
        $this->tmpDir = $this->appDir . '/var/tmp';
        $this->logDir = $this->appDir . '/var/log';
    }
}
