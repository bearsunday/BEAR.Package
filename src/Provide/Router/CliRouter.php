<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use Aura\Cli\CliFactory;
use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Status;
use Aura\Cli\Stdio;
use BEAR\Package\Annotation\StdIn;
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
     * @var Stdio
     */
    private $stdIo;

    /**
     * @var string
     */
    private $stdIn;

    /**
     * @var null|\Exception
     */
    private $terminateException;

    /**
     * @Named("original")
     */
    public function __construct(RouterInterface $router, Stdio $stdIo = null)
    {
        $this->router = $router;
        $this->stdIo = $stdIo ?: (new CliFactory)->newStdio();
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        file_exists($this->stdIn) && unlink($this->stdIn);
    }

    public function __wakeup()
    {
        $this->stdIo = (new CliFactory)->newStdio();
    }

    public function setTerminateException(\Exception $e)
    {
        $this->terminateException = $e;
    }

    /**
     * @Inject
     * @StdIn
     */
    public function setStdIn(string $stdIn)
    {
        $this->stdIn = $stdIn;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        $this->validateArgs($globals);
        list($method, $query, $server) = $this->parseGlobals($globals);
        $this->setQuery($method, $query, $globals, $server);

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
     * Set user input query to $globals or &$server
     */
    private function setQuery(string $method, array $query, array &$globals, array &$server)
    {
        if ($method === 'get') {
            $globals['_GET'] = $query;

            return;
        }
        if ($method === 'post') {
            $globals['_POST'] = $query;

            return;
        }
        $server = $this->getStdIn($method, $query, $server);
    }

    private function error(string $command)
    {
        $help = new CliRouterHelp(new OptionFactory);
        $this->stdIo->outln($help->getHelp($command));
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    private function terminate(int $status)
    {
        if ($this->terminateException instanceof \Exception) {
            throw $this->terminateException;
        }
        // @codeCoverageIgnoreStart
        exit($status);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Return StdIn in PUT, PATCH or DELETE
     */
    private function getStdIn(string $method, array $query, array &$server) : array
    {
        if ($method === 'put' || $method === 'patch' || $method === 'delete') {
            $server[HttpMethodParams::CONTENT_TYPE] = HttpMethodParams::FORM_URL_ENCODE;
            file_put_contents($this->stdIn, http_build_query($query));

            return $server;
        }

        return $server;
    }

    private function validateArgs(array $globals)
    {
        if ($globals['argc'] < 3) {
            $this->error(basename($globals['argv'][0]));
            $this->terminate(Status::USAGE);
        }
    }

    /**
     * Return $method, $query, $server from $globals
     */
    private function parseGlobals(array $globals) : array
    {
        list(, $method, $uri) = $globals['argv'];
        $parsedUrl = parse_url($uri);
        $query = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }
        $server = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $parsedUrl['path']
        ];

        return [$method, $query, $server];
    }
}
