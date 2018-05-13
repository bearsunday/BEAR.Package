<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

use BEAR\Package\Unlink;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/hash.php';

(new Unlink)(__DIR__ . '/tmp');
(new Unlink)(__DIR__ . '/Fake/fake-app/var/tmp');
(new Unlink)(__DIR__ . '/Fake/fake-app/var/log');
(new Unlink)(dirname(__DIR__) . '/var/tmp');
