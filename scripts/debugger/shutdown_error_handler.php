<?php


return function () use ($getFile, $getTraceList) {
    $xstack = (function_exists('xdebug_get_function_stack')) ? xdebug_get_function_stack() : [];
    if (PHP_SAPI === 'cli') {
        return;
    }
    $type = $message = $file = $line = $trace = '';
    $error = error_get_last();
    if (!$error) {
        exit(0);
    }
    extract($error);
    // redirect
    if ($type == E_PARSE) {
        $back = $_SERVER['REQUEST_URI'];
        header("Location: /_dev/edit/index.php?file={$file}&line={$line}&error={$message}&back={$back}");
    }
    // Logic error only
    if (!in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR])) {
        exit(0);
    }
    error_log(ob_get_clean());
    http_response_code(500);
    $html = include __DIR__ . '/shutdown_error_handler/error.tpl.php';
    echo $html;
    exit(1);
};