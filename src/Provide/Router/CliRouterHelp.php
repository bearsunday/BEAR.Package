<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Router;

use Aura\Cli\Help;

class CliRouterHelp extends Help
{
    protected function init()
    {
        $this->setSummary('CLI Router');
        $this->setUsage('<method> <resource URI path with query>');
        $this->setDescr('Available methods are [get|post|put|patch|delete|options].');
    }
}
