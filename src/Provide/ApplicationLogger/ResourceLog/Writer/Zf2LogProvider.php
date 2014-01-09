<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Sunday\Inject\LogDirInject;
use Ray\Di\ProviderInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Logger;
use Zend\Log\Writer\Db;
use Zend\Log\Writer\Syslog;

/**
 * Zf2 logger provider
 */
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
        static $zf2Log;

        if (! $zf2Log) {
            $this->db->query(
                'CREATE TABLE IF NOT EXISTS log(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, timestamp, message, priority, priorityName, extra_page)',
                Adapter::QUERY_MODE_EXECUTE
            );
            $writer = new Db($this->db, 'log');
            $this->zf2Log->addWriter($writer);
            $zf2Log = $this->zf2Log;
        }

        return $zf2Log;
    }
}
