<?php

namespace BEAR\Package\Provide\Error;

class ErrorPageTest extends \PHPUnit_Framework_TestCase
{
    private $errorPage;

    public function setUp()
    {
        parent::setUp();
        $this->errorPage = new ErrorPage('some_text_after_error_message');
    }

    public function testToString()
    {
        $text = (string) $this->errorPage;
        $this->assertContains('some_text_after_error_message', $text);
    }
}
