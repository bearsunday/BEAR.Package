<?php
/** @noinspection PhpUndefinedNamespaceInspection */
trait AppSetupTrait
{
    /**
     * Resource client
     *
     * @var \BEAR\Resource\Resource
     */
    private $resource;

    /**
     * @global $mode;
     */
    protected function setUp()
    {
        static $app;

        if (!$app) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $mode = 'Test';
            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpIncludeInspection */
            $app = require App::DIR . '/scripts/instance.php';
        }

        $this->resource = $app->resource;
    }
}
