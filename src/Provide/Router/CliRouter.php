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

    public function setTerminateException(\Exception $e) : void
    {
        $this->terminateException = $e;
    }

    /**
     * @Inject
     * @StdIn
     */
    public function setStdIn(string $stdIn) : void
    {
        $this->stdIn = $stdIn;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
        $this->validateArgs($globals);
        [$method, $query, $server] = $this->parseGlobals($globals);
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
    private function setQuery(string $method, array $query, array &$globals, array &$server) : void
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

    private function error(string $command) : void
    {
        $help = new CliRouterHelp(new OptionFactory);
        $this->stdIo->outln($help->getHelp($command));
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    private function terminate(int $status) : void
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

    private function validateArgs(array $globals) : void
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
        [, $method, $uri] = $globals['argv'];
        $urlQuery = parse_url($uri, PHP_URL_QUERY);
        $urlPath = parse_url($uri, PHP_URL_PATH);
        $query = [];
        if ($urlQuery) {
            parse_str($urlQuery, $query);
        }
        $server = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $urlPath
        ];

        return [$method, $query, $server];
    }
}
