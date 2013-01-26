<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal\Interceptor;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\SQLLogger;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Cache interceptor
 *
 * @package    BEAR.Sunday
 * @subpackage Intercetor
 */
final class DbInjector implements MethodInterceptor
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var DebugStack
     */
    private $sqlLogger;

    /**
     * DSN for master
     *
     * @var array
     */
    private $masterDb;

    /**
     * DSN for slave
     *
     * @var array
     */
    private $slaveDb;

    /**
     * Set annotation reader
     *
     * @param Reader $reader
     *
     * @return void
     * @Inject
     */
    public function setReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Set SqlLogger
     *
     * @param \Doctrine\DBAL\Logging\SQLLogger $sqlLogger
     *
     * @Inject(optional = true)
     */
    public function setSqlLogger(SQLLogger $sqlLogger)
    {
        $this->sqlLogger = $sqlLogger;
    }

    /**
     * Constructor
     *
     * @param  array $masterDb
     * @@param array $slaveDb
     *
     * @Inject
     * @Named("masterDb=master_db,slaveDb=slave_db")
     */
    public function __construct(array $masterDb, array $slaveDb)
    {
        $this->masterDb = $masterDb;
        $this->slaveDb = $slaveDb;
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Aop.MethodInterceptor::invoke()
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        $method = $invocation->getMethod();
        $connectionParams = ($method->name === 'onGet') ? $this->slaveDb : $this->masterDb;
        $pagerAnnotation = $this->reader->getMethodAnnotation($method, 'BEAR\Sunday\Annotation\DbPager');
        if ($pagerAnnotation) {
            $connectionParams['wrapperClass'] = 'BEAR\Package\Module\Database\Dbal\PagerConnection';
            $db = DriverManager::getConnection($connectionParams);
            /** @var $db \BEAR\Package\Module\Database\Dbal\PagerConnection */
            $db->setMaxPerPage($pagerAnnotation->limit);
        } else {
            $db = DriverManager::getConnection($connectionParams);
        }
        /* @var $db \BEAR\Package\Module\Database\Dbal\PagerConnection */

        if ($this->sqlLogger instanceof SQLLogger) {
            $db->getConfiguration()->setSQLLogger($this->sqlLogger);
        }
        $object->setDb($db);
        $result = $invocation->proceed();
        if ($this->sqlLogger instanceof DebugStack) {
            $this->sqlLogger->stopQuery();
            $object->headers['x-sql'] = [$this->sqlLogger->queries];
        } elseif ($this->sqlLogger instanceof SQLLogger) {
            $this->sqlLogger->stopQuery();
        }
        if ($pagerAnnotation) {
            $pagerData = $db->getPager();
            if ($pagerData) {
                $object->headers['pager'] = $pagerData;
            }
        }

        return $result;
    }
}
