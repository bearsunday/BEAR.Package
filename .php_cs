<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->notName('view.php')
    ->exclude('docs')
    ->exclude('vendor')
    ->exclude('.idea')
    ->in(__DIR__ . '/src')
;

return Symfony\CS\Config\Config::create()
    ->finder($finder)
;