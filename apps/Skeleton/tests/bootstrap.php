<?php

// dev tools
require_once dirname(__DIR__) . '/scripts/bootstrap.php';
require_once dirname(__DIR__) . '/scripts/bootstrap/dev_instance.php';

// application root
chdir(dirname(__DIR__));

require 'scripts/clear.php';