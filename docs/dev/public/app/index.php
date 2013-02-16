<?php

if (isset($_GET['text'])) {
    echo serialize($app);
} else {
    print_o($app);
}
