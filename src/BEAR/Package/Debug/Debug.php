<?php

namespace BEAR\Package\Debug;

/**
 * Debug tools
 */
class Debug
{
    /**
     * debug print
     *
     * @param array $trace
     * @param null  $var
     * @param int   $level
     */
    public static function printR(array $trace, $var = null, $level = 2)
    {
        list($label, $varName, $file, $line, $method) = self::getMetaInfo($trace);
        $varDump = self::getVarString($var, $level);

        if (PHP_SAPI === 'cli') {
            self::outputCli($var, $varName, $file, $line, $method);
            return;
        }

        self::fire($var, $trace[0]);
        echo "{$label}{$varDump}</div>";
    }

    /**
     * @param $var
     * @param $level
     *
     * @return string
     */
    private static function getVarString($var, $level)
    {
        // contents
        $htmlErrors = ini_get('html_errors');
        ini_set('html_errors', 'On');

        $isCli = (PHP_SAPI === 'cli');
        if (extension_loaded('xdebug')) {
            if ($isCli) {
                ini_set('xdebug.xdebug.cli_color', true);
            }
            ini_set('xdebug.var_display_max_depth', $level);
        }
        $er = error_reporting(0);
        ob_start();
        var_dump($var); // sometimes notice with reason unknown
        $varDump = ob_get_clean();
        error_reporting($er);

        if ($isCli) {
            $varDump = strip_tags(html_entity_decode($varDump));
        }
        ini_set('html_errors', $htmlErrors);

        return $varDump;
    }

    /**
     * @param array $trace
     *
     * @return array [$label, $varName, $file, $line, $method]
     */
    private static function getMetaInfo(array $trace)
    {
        // label
        $receiver = $trace[0];

        $file = $receiver['file'];
        $line = $receiver['line'];
        $funcName = $receiver['function'];
        $method = (isset($trace[1]['class'])) ? " ({$trace[1]['class']}" . '::' . "{$trace[1]['function']})" : '';
        $fileArray = file($file);
        $p = trim($fileArray[$line - 1]);
        unset($fileArray);
        preg_match("/{$funcName}\((.+)[\s,\)]/is", $p, $matches);
        if (isset($matches[1])) {
            $varName = $matches[1];
        } else {
            $varName = '(void)';
        }
        $file = "<a href=\"/dev/edit/index.php?file={$file}&line={$line}\">$file</a>";
        $varNameCss = <<<EOT
    background-color: green;
    color: white;
    border: 1px solid #E1E1E8;
    padding: 2px 4px;
    margin 20px 40px;
EOT;
        $fileCss = <<<EOT
    background-color: #F7F7F9;
    color: #DD1144;
    border: 1px solid #E1E1E8;
    padding: 2px 4px;
EOT;
        $file = <<<EOT
<span style="font-size:12px;color:gray"> in {$file} on line <b>{$line}</b> <code>$method</code></span>
EOT;
        $label = <<<EOT
<span style="$varNameCss">$varName</span><span style="$fileCss">$file</span>
EOT;
        return [$label, $varName, $file, $line, $method];
    }


    /**
     * @param string $var
     * @param array  $receiver
     *
     * @return void
     */
    private static function fire($var, array $receiver)
    {
        if (class_exists('FB', false)) {
            $label = __FUNCTION__ . '() in ' . $receiver['file'] . ' on line ' . $receiver['line'];
            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            FB::group($label);
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedClassInspection */
            FB::error($var);
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedClassInspection */
            FB::groupEnd();
        }
    }

    /**
     * @param string $var
     * @param string $varName
     * @param string $file
     * @param int    $line
     * @param string $method
     *
     * @return void
     */
    private static function outputCli($var, $varName, $file, $line, $method)
    {
        $colorOpenReverse = "\033[7;32m";
        $colorOpenBold = "\033[1;32m";
        $colorOpenPlain = "\033[0;32m";
        $colorClose = "\033[0m";
        echo $colorOpenReverse . "$varName" . $colorClose . " = ";
        var_dump($var);
        echo $colorOpenPlain;
        echo "in {$colorOpenBold}{$file}{$colorClose}{$colorOpenPlain} on line {$line}$method";
        echo $colorClose . "\n";
    }
}
