<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

/**
 * @since 0.10.0
 */
class CurrentPhpExecutable
{
    /**
     * @return string
     */
    public static function getExecutable()
    {
        if (array_key_exists('PHP_COMMAND', $_SERVER)) {
            $executable = $_SERVER['PHP_COMMAND'];
        } elseif (array_key_exists('_', $_SERVER)) {
            $executable = $_SERVER['_'];
        } else {
            $executable = $_SERVER['argv'][0];
        }

        if (preg_match('!^/cygdrive/([a-z])/(.+)!', $executable, $matches)) {
            $executable = $matches[1] . ':\\' . str_replace('/', '\\', $matches[2]);
        }

        if (strtolower(substr(PHP_OS, 0, strlen('win'))) == 'win') {
            putenv(sprintf('ENVPATH="%s"', $executable));

            return '%ENVPATH%';
        } else {
            return $executable;
        }
    }

    /**
     * @return string
     */
    public static function getConfigFile()
    {
        $configFile = get_cfg_var('cfg_file_path');
        if ($configFile !== false) {
            return $configFile;
        }

        return null;
    }
}
