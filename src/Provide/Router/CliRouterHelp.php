<?php declare(strict_types=1);
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
        $this->setUsage('<method> <uri>');
        $this->setDescr("E.g. \"get /\", \"options /users\", \"post 'app://self/users?name=Sunday'\"");
    }
}
