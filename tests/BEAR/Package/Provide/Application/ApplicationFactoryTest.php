<?php

namespace Mock\Module {
    class StandardModule extends \Ray\Di\AbstractModule
    {
        public function configure()
        {
            $this->bind('BEAR\Sunday\Extension\Application\AppInterface')->to('Mock\App');
        }
    }
}

namespace Mock {

    use \BEAR\Sunday\Extension\Application\AppInterface;

    class App implements AppInterface
    {
    }
}

namespace BEAR\Package\Provide\Application {


    class ApplicationFactoryTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @var ApplicationFactory
         */
        protected $applicationFactory;

        protected function setUp()
        {
            $this->applicationFactory = new ApplicationFactory(new \Doctrine\Common\Cache\ArrayCache);
        }

        protected function tearDown()
        {
        }

        public function testNew()
        {
            $this->assertInstanceOf('BEAR\Package\Provide\Application\ApplicationFactory', $this->applicationFactory);
        }

        /**
         * @covers BEAR\Package\Provide\Application\ApplicationFactory::newInstance
         */
        public function testNewInstance()
        {
            $app = $this->applicationFactory->newInstance('Mock', 'Standard');
            $this->assertInstanceOf('Mock\App', $app);
        }

        /**
         * @covers BEAR\Package\Provide\Application\ApplicationFactory::newInstance
         */
        public function testNewInstanceSandboxApp()
        {
            $app = $this->applicationFactory->newInstance('Sandbox', 'Prod');
            $this->assertInstanceOf('Sandbox\App', $app);
        }

        /**
         * @expectedException \BEAR\Package\Provide\Application\Exception\InvalidMode
         */
        public function testNewInstanceInvalidMode()
        {
            $this->applicationFactory->newInstance('Sandbox', 'NON_VALID');
        }

        public function testNewInstanceCached()
        {
            $this->applicationFactory->newInstance('Mock', 'Standard');
            $app = $this->applicationFactory->newInstance('Mock', 'Standard');
            $this->assertInstanceOf('Mock\App', $app);
        }

    }
}
