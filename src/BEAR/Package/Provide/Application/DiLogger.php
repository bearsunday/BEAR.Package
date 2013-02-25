<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Application;

use Ray\Aop\Bind;
use Ray\Di\LoggerInterface;

/**
 * Di logger
 */
class DiLogger implements LoggerInterface
{
    /**
     * @var string
     */
    public $logMessage = '';

    /**
     * log injection information
     *
     * @param string        $class
     * @param array         $params
     * @param array         $setter
     * @param object        $object
     * @param \Ray\Aop\Bind $bind
     */
    public function log($class, array $params, array $setter, $object, Bind $bind)
    {
        $toStr = function ($params) {
            foreach ($params as &$param) {
                if (is_object($param)) {
                    $param = '(' . get_class($param) . ')';
                } elseif (is_scalar($param)) {
                    $param = '(' . gettype($param) . ') ' . $param;
                } elseif (is_callable($param)) {
                    $param = '(Callable)';
                }
            }
            return implode(', ', $params);
        };
        $constructor = $toStr($params);
        $constructor = $constructor ? $constructor : '';
        $setter = $setter ? "setter[" . implode(', ', array_keys($setter)) . ']': '';
        $logMessage = "[DI] {$class} construct[$constructor] {$setter}";
        $this->logMessage = $logMessage;
    }
}
