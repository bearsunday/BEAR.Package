<?php
/**
 * Return db connection
 *
 * @var PDO
 *
 * @global $_ENV['BEAR_DB_ID']
 * @global $_ENV['BEAR_DB_PASSWORD']
 */
$id = isset($_SERVER['BEAR_DB_ID']) ? $_SERVER['BEAR_DB_ID'] : 'root';
$password = isset($_SERVER['BEAR_DB_PASSWORD']) ? $_SERVER['BEAR_DB_PASSWORD'] : '';
return new \PDO("mysql:host=localhost; dbname=blogbeartest", $id, $password);
