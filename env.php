<?php
use josegonzalez\Dotenv\Loader;

$env = __DIR__ . '/.env';
if (file_exists($env)) {
    (new Loader($env))->parse()->putenv(true);
}
