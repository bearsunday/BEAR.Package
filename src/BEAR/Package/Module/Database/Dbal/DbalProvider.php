<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal;

use Ray\Di\ProviderInterface as Provide;


/**
 * PDO provider
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class DbalProvider implements Provide
{
    /**
     * @var array
     */
    private $dsn;

    /**
     * Set DSN
     *
     * @param string $dsn
     *
     * @Inject
     * @Named("dsn=dsn");
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * Return instance
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function get()
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = [
            'driver' => 'pdo_sqlite',
            'path' => $this->dsn,
            'user' => null,
            'password' => null
        ];
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        return $conn;
    }
}
