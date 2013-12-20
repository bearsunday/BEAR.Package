<?php
/**
 * Application instance script
 *
 * @return $app  \BEAR\Sunday\Extension\Application\AppInterface
 *
 * @global $context string configuration context
 */
namespace Demo\Sandbox;

require_once __DIR__ . '/autoload.php';

$context = isset($context) ? $context : 'prod';
return \BEAR\Bootstrap\getApp(__NAMESPACE__, $context);
