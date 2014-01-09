<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Ray\Di\ProviderInterface as Provide;

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
        $config = new Configuration;
        $connectionParams = [
            'driver' => 'pdo_sqlite',
            'path' => $this->dsn,
            'user' => null,
            'password' => null
        ];
        $conn = DriverManager::getConnection($connectionParams, $config);

        return $conn;
    }
}
