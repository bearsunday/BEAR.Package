<?php
/**
 * Return db connection
 *
 * @var PDO
 *
 */
$db = new \PDO('sqlite::memory:');
$db->query('CREATE TABLE "posts" (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,title VARCHAR(50),body TEXT,created DATETIME DEFAULT NULL,modified DATETIME DEFAULT NULL);');

return $db;
