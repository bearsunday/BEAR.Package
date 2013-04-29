<?php

if (isset($_GET['text'])) {
    echo serialize($app);
} elseif (isset($_GET['print_r'])) {
    echo print_r($app);
} else {
    print_o($app);
}
