<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal\Interceptor;

use Doctrine\DBAL\Driver\Connection as DriverConnection;

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
