<?php

namespace BEAR\Package\Dev\Debug;

/**
 * Debug utility
 */
class Debug
{
    /**
     * Debug print
     *
     * @param array $trace
     * @param null  $var
     * @param int   $level
     */
    public static function printR(array $trace, $var = null, $level = 2)
    {
        list ($htmlErrors, $isCli) = self::init($level);
        $varDump = self::getVarDump($var, $isCli);
        ini_set('html_errors', $htmlErrors);

        // label
        $receiver = $trace[0];
        $method = (isset($trace[1]['class'])) ? " ({$trace[1]['class']}" . '::' . "{$trace[1]['function']})" : '';
        preg_match(
            "/{$receiver['function']}\((.+)[\s,\)]/is",
            trim(file($receiver['file'])[$receiver['line'] - 1]),
            $matches
        );
        list($varName, $varDump)  = isset($matches[1]) ? [$matches[1], $varDump] : ['(void)', '<br>'];

        if ($isCli) {
            self::outputCli($varName, $var, $receiver, $method);
            return;
        }
        if (class_exists('FB', false)) {
            self::outputFb($var, $receiver['file'], $receiver['line']);
        }

        $label = self::getLabel($receiver['file'], $receiver['line'], $varName, $method);
        // output
        echo "{$label}{$varDump}</div>";
    }

    /**
     * @param int $level
     *
     * @return array
     */
    private static function init($level)
    {
        // contents
        $isCli = (PHP_SAPI === 'cli');
        $htmlErrors = ini_get('html_errors');
        if (! extension_loaded('xdebug')) {
            ini_set('html_errors', 'On');
            return [$htmlErrors, $isCli];
        }
        ini_set('xdebug.xdebug.cli_color', true);
        ini_set('xdebug.var_display_max_depth', $level);
        return [$htmlErrors, $isCli];
    }

    /**
     * @param mixed $var
     * @param bool  $isCli
     *
     * @return string
     */
    private static function getVarDump($var, $isCli)
    {
        $reporting = error_reporting(0);
        ob_start();
        var_dump($var); // sometimes notice with reason unknown
        $varDump = ob_get_clean();
        error_reporting($reporting);
        if ($isCli) {
            $varDump = strip_tags(html_entity_decode($varDump));
        }

        return $varDump;
    }

    /**
     * Console output
     *
     * @param string $varName
     * @param string $var
     * @param array  $receiver
     * @param string $method
     *
     * @return void
     */
    private static function outputCli($varName, $var, array $receiver, $method)
    {
        $colorOpenReverse = "\033[7;32m";
        $colorOpenBold = "\033[1;32m";
        $colorOpenPlain = "\033[0;32m";
        $colorClose = "\033[0m";
        echo "{$colorOpenReverse}{$varName}{$colorClose} = ";
        var_dump($var);
        echo $colorOpenPlain . "in {$colorOpenBold}{$receiver['file']}{$colorClose}{$colorOpenPlain}";
        echo "on line {$receiver['line']}{$method}{$colorClose}\n";
    }

    /**
     * FirePHP output
     *
     * @param mixed  $var
     * @param string $file
     * @param int    $line
     */
    private static function outputFb($var, $file, $line)
    {
        $label = __FUNCTION__ . "() in {$file} on line {$line}";
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

    /**
     * @param string $file
     * @param int    $line
     * @param string $varName
     * @param string $method
     *
     * @return string
     */
    private static function getLabel($file, $line, $varName, $method)
    {
        $file = "<a href=\"/dev/edit/index.php?file={$file}&line={$line}\">$file</a>";
        $varNameCss = "background-color: green; color: white; border: 1px solid #E1E1E8; padding: 2px 4px; margin 20px 40px;";
        $fileCss = "background-color: #F7F7F9; color: #DD1144; border: 1px solid #E1E1E8; padding: 2px 4px;";
        $file = <<<EOT
<span style="font-size:12px;color:gray"> in {$file} on line <b>{$line}</b> <code>$method</code></span>
EOT;
        $label = <<<EOT
<span style="$varNameCss">$varName</span><span style="$fileCss">$file</span>
EOT;

        return $label;
    }
}
