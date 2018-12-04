<?php

declare(strict_types=1);

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
