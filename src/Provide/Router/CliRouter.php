<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Status;
use BEAR\Package\AbstractAppMeta;
use BEAR\Package\AppMeta;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Aura\Cli\CliFactory;

class CliRouter implements RouterInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AbstractAppMeta
     */
    private $appMeta;


    /**
     * @param AbstractAppMeta $appMeta
     *
     * @Inject
     */
    public function setAppMeta(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
        ini_set('error_log', $appMeta->logDir . '/console.log');
    }
    /**
     * @param RouterInterface $router
     *
     * @Inject
     * @Named("original")
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals = [])
    {
        if ($globals['argc'] !== 3) {
            $this->error(Status::USAGE, basename($globals['argv'][0]));
        };
        list(, $method, $uri) = $globals['argv'];
        $parsedUrl = parse_url($uri);
        $query = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }

        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => $method,
                'REQUEST_URI' => $parsedUrl['path']
            ],
            '_GET' => $query,
            '_POST' => $query
        ];

        return $this->router->match($globals);
    }

    /**
     * @param string $status
     * @param string $message
     */
    private function error($status, $command)
    {
        $cliFactory = new CliFactory;
        $stdio = $cliFactory->newStdio();

        $help = new CliRouterHelp(new OptionFactory);
        $stdio->outln($help->getHelp($command));
        exit($status);
    }
}
