<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use PHPUnit\Framework\TestCase;

class ErrorPageTest extends TestCase
{
    private $errorPage;

    protected function setUp()
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
