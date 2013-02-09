<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use Ray\Di\ProviderInterface;
use Zend\Log\Logger;
use BEAR\Sunday\Inject\LogDirInject;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Writer\Db;
use Zend\Log\Writer\Syslog;

final class Zf2LogProvider implements ProviderInterface
{
    /**
     * @var Adapter
     */
    private $db;

    /**
     * @var \Zend\Log\Logger
     */
    private $zf2Log;

    /**
     * @param $logDir string
     */
    public function __construct($logDir)
    {
        $this->zf2Log = new Logger;
        $this->zf2Log->addWriter(new Syslog);
        $dbConfig = [
            'driver' => 'Pdo_Sqlite',
            'dsn' => 'sqlite:' . $logDir . '/resource.db'
        ];
        $this->db = new Adapter($dbConfig);
    }
    /**
     * @return Logger
     */
    public function get()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS log(timestamp, message, priority, priorityName, extra_page)', Adapter::QUERY_MODE_EXECUTE);
        $writer = new Db($this->db, 'log');
        $this->zf2Log->addWriter($writer);

        return $this->zf2Log;
    }
}
