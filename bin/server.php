<?php

// options
$appDir = isset($argv[$argc - 1]) ? $argv[$argc - 1] : error();
$opt = getopt('', [ "context::", "port::", "php::" ]);
$port = isset($opt['port']) ? $opt['port'] : '8080';
$context = isset($opt['context']) ? $opt['context'] : 'dev';
$php = isset($opt['php']) ? $opt['php'] : 'php';
$router = dirname(__DIR__) . "/{$appDir}/bootstrap/contexts/{$context}.php";

if (! file_exists($router)) {
    error("invalid context:{$context}");
}

$root =  dirname(__DIR__) . "/{$appDir}/var/www/";
$cmd = "{$php} -S 0.0.0.0:{$port} -t {$root} {$router}";
$spec = [0 => STDIN, 1 => STDOUT, 2 => STDERR];

echo "Starting the BEAR.Sunday development server:{$router}"  . PHP_EOL;
// run the command as a process
$process = proc_open($cmd, $spec, $pipes, $root);
proc_close($process);

/**
 * @param string $msg
 */
function error($msg = 'Usage: php bin/server.php <--port=port> <--context=context> <--php=php_bin_path> app-dir')
{
    error_log($msg);
    exit(1);
}
