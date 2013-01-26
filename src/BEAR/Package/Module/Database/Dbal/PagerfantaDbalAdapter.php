<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal;

use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * DoctrineDbal adapter.
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class PagerfantaDbalAdapter implements AdapterInterface
{
    /**
     * Constructor
     *
     * @param DriverConnection $db
     * @param string           $query
     */
    public function __construct(DriverConnection $db, $query)
    {
        $this->query = new PagingQuery($db, $query);
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return count($this->query);
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $this->query->setOffsetLength($offset, $length);
        $iterator = $this->query->getIterator();

        return $iterator;
    }
}
