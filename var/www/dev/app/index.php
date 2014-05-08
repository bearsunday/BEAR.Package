<?php

/**
 * @global $app
 */
use Printo\Printo;

if (isset($_GET['text'])) {
    echo serialize($app);
} elseif (isset($_GET['print_r'])) {
    echo print_r($app);
} else {
    echo (new Printo($app));
}
