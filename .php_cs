<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->notName('view.php')
    ->notName('preloader.php')
    ->exclude('docs')
    ->exclude('vendor')
    ->exclude('.idea')
    ->exclude('cs')
    ->notName('*.tpl.php')
    ->notName('*.xml')
    ->in(__DIR__ . '/src')
;

return Symfony\CS\Config\Config::create()
    ->finder($finder)
;