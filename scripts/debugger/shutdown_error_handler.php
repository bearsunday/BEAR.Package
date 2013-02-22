<?php
use BEAR\Ace\Editor\Editor;
return function() {
    if (PHP_SAPI === 'cli') {
        return;
    }
    $type = $message = $file = $line = $trace = '';
    $error = error_get_last();
    if (! $error) {
        return;
    }
    extract($error);
    // redirect
    if ($type == E_PARSE) {
        ob_end_clean();
        $base = dirname(dirname(dirname(__DIR__)));
        $file = str_replace($base, '', $file);
        echo (new Editor)
            ->setBasePath($base)
            ->setPath($file)
            ->setLine($line)
            ->setMessage($message)
            ->setSaveUrl('/dev/edit/save.php');
        exit();
    }
    // Logic error only
    if (!in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR])) {
        return;
    }
    error_log(ob_get_clean());
    http_response_code(500);
    $html = include __DIR__ . '/shutdown_error_handler/view.php';
    echo $html;
    exit(1);
};
