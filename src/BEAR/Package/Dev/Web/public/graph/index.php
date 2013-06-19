<?php

$app = apc_fetch($_GET['app']);
print_o($app);
