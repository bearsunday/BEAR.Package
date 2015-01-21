<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Cli\CliFactory;
use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Status;
use BEAR\Package\AbstractAppMeta;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

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
    public function match(array $globals, array $server)
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
            '_GET' => $query,
            '_POST' => $query
        ];
        $server = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $parsedUrl['path']
        ];

        return $this->router->match($globals, $server);
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $data)
    {
        return $this->router->generate($name, $data);
    }

    /**
     * @param string $status
     * @param string $command
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
