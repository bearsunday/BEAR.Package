<?php

namespace BEAR\Package\Dev\Resource;

class ResourceLogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  ResourceLog;
     */
    protected $log;

    protected function setUp()
    {
        $this->file = __DIR__ . '/resource.test.db';
        $this->log = new ResourceLog($this->file);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Dev\Resource\ResourceLog', $this->log);
    }

    public function testToTable()
    {
        $table = $this->log->toTable();
        $this->assertContains('<th>Status</th>', $table);
        return $table;
    }

    public function testToTableExceptionReturnEmptyString()
    {
        $table = (new ResourceLog(''))->toTable();
        $this->assertEmpty($table);
    }
}

