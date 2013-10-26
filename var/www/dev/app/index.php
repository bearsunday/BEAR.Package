<?php

use Printo\Printo;

if (isset($_GET['text'])) {
    echo serialize($app);
} elseif (isset($_GET['print_r'])) {
    echo print_r($app);
} else {
    $progress = (!isset($_GET['all']));
    Printo::init(
        ['showProgressive' => $progress]
    );
    echo (new Printo($app));
}
