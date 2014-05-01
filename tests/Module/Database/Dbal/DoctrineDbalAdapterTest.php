<?php

namespace BEAR\Package\tests\Module\Database\Dbal;

use PDO;
use Doctrine\DBAL\DriverManager;
use BEAR\Package\Module\Database\Dbal\PagerfantaDbalAdapter;

/**
 * Test class for Pager.
 */
class DoctrineDbalAdapterTest extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var PagerfantaDbalAdapter
     */
    private $adapter;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $sql;

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $this->pdo = require __DIR__ . '/scripts/db.php';

        return $this->createDefaultDBConnection($this->pdo, ':memory:');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return $this->createFlatXmlDataSet(dirname(__DIR__) . '/mock/pager_seed.xml');
    }

    protected function setUp()
    {
        parent::setUp();
        $params['pdo'] = $this->pdo;
        $db = DriverManager::getConnection($params);
        $this->sql = 'SELECT * FROM posts';
        $this->adapter = new PagerfantaDbalAdapter($db, $this->sql);
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Package\Module\Database\dbal\PagerfantaDbalAdapter', $this->adapter);
    }

    public function testCount()
    {
        $count = $this->adapter->getNbResults();
        $this->assertSame(5, $count);
    }

    public function testGetSlice()
    {
        $offset = 1;
        $length = 2;
        $result = $this->adapter->getSlice($offset, $length);
        $this->assertSame(2, (integer) $result[0]['id']);
        $this->assertSame(3, (integer) $result[1]['id']);
        $this->assertSame(2, count($result));
    }
}
