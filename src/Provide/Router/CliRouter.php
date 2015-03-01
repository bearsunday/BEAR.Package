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
use Aura\Cli\Stdio;
use BEAR\AppMeta\AbstractAppMeta;
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
     * @var \LogicException
     */
    private $exception;

    /**
     * @var Stdio
     */
    private $stdIo;

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
    public function __construct(RouterInterface $router, \LogicException $exception = null, Stdio $stdIo = null)
    {
        $this->router = $router;
        $this->exception = $exception;
        $this->stdIo = $stdIo ?: (new CliFactory)->newStdio();
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        if ($globals['argc'] !== 3) {
            $this->error(basename($globals['argv'][0]));
            $this->exitProgram(Status::USAGE);
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
     * @param string $command
     */
    private function error($command)
    {
        $help = new CliRouterHelp(new OptionFactory);
        $this->stdIo->outln($help->getHelp($command));
    }

    private function exitProgram($status)
    {
        if ($this->exception) {
            throw $this->exception;
        }
        // @codeCoverageIgnoreStart
        exit($status);
        // @codeCoverageIgnoreEnd
    }
}
