<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal\Interceptor;

use Doctrine\DBAL\Driver\Connection as DriverConnection;

/**
 * Interface for Db setter
 *
 * @package    BEAR.Sunday
 * @subpackage Intercetor
 */
interface DbSetterInterface
{
    /**
     * Set db connection
     *
     * @param DriverConnection $db
     *
     * @return void
     */
    public function setDb(DriverConnection $db = null);
}
