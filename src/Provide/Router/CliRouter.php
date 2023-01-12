<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use Aura\Cli\CliFactory;
use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Status;
use Aura\Cli\Stdio;
use BEAR\Package\Annotation\StdIn;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Exception;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Throwable;

use function basename;
use function file_exists;
use function file_put_contents;
use function http_build_query;
use function parse_str;
use function parse_url;
use function strtoupper;
use function unlink;

use const PHP_URL_PATH;
use const PHP_URL_QUERY;

/**
 * @psalm-import-type Globals from RouterInterface
 * @psalm-import-type Server from RouterInterface
 * @psalm-type CliServer = array{
 *     argc: int,
 *     argv: array<int, string>,
 *     REQUEST_URI: string,
 *     REQUEST_METHOD: string,
 *     CONTENT_TYPE?: string,
 *     HTTP_CONTENT_TYPE?: string,
 *     HTTP_RAW_POST_DATA?: string
 * }
 */
class CliRouter implements RouterInterface
{
    private Stdio $stdIo;
    private string $stdIn = '';
    private Throwable|null $terminateException = null;

    public function __construct(
        #[Named('original')]
        private RouterInterface $router,
        Stdio|null $stdIo = null,
    ) {
        $this->stdIo = $stdIo ?: (new CliFactory())->newStdio();
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        file_exists($this->stdIn) && unlink($this->stdIn);
    }

    public function __wakeup(): void
    {
        $this->stdIo = (new CliFactory())->newStdio();
    }

    public function setTerminateException(Throwable $e): void
    {
        $this->terminateException = $e;
    }

    #[Inject]
    public function setStdIn(
        #[StdIn]
        string $stdIn,
    ): void {
        $this->stdIn = $stdIn;
    }

    /**
     * {@inheritdoc}
     *
     * @param Globals $globals
     * @param Server  $server
     */
    public function match(array $globals, array $server)
    {
        /** @var CliServer $server */
        $this->validateArgs($server['argc'], $server['argv']);
        // covert console $_SERVER to web $_SERVER $GLOBALS
        /** @psalm-suppress InvalidArgument */
        [$method, $query, $server] = $this->parseServer($server);
        /** @psalm-suppress MixedArgumentTypeCoercion */
        [$webGlobals, $webServer] = $this->addQuery($method, $query, $globals, $server); // @phpstan-ignore-line

        return $this->router->match($webGlobals, $webServer);
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
     *
     * @param array<string, array<string, mixed>> $query
     * @param Globals                             $globals
     * @param Server                              $server
     *
     * @return array{0:Globals, 1:Server}
     */
    private function addQuery(string $method, array $query, array $globals, array $server): array
    {
        if ($method === 'get') {
            $globals['_GET'] = $query;

            return [$globals, $server];
        }

        if ($method === 'post') {
            $globals['_POST'] = $query;

            return [$globals, $server];
        }

        $server = $this->getStdIn($method, $query, $server);

        return [$globals, $server];
    }

    private function error(string $command): void
    {
        $help = new CliRouterHelp(new OptionFactory());
        $this->stdIo->outln($help->getHelp($command));
    }

    /** @SuppressWarnings(PHPMD) */
    private function terminate(int $status): void
    {
        if ($this->terminateException instanceof Exception) {
            throw $this->terminateException;
        }

        // @codeCoverageIgnoreStart
        exit($status);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Return StdIn in PUT, PATCH or DELETE
     *
     * @param  array<string, array<string, mixed>|string> $query
     * @param Server                                     $server
     *
     * @return Server
     */
    private function getStdIn(string $method, array $query, array $server): array
    {
        if ($method === 'put' || $method === 'patch' || $method === 'delete') {
            $server[HttpMethodParams::CONTENT_TYPE] = HttpMethodParams::FORM_URL_ENCODE;
            file_put_contents($this->stdIn, http_build_query($query));

            return $server;
        }

        return $server;
    }

    /** @param array<int, string> $argv */
    private function validateArgs(int $argc, array $argv): void
    {
        if ($argc >= 3) {
            return;
        }

        $this->error(basename($argv[0]));
        $this->terminate(Status::USAGE);
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd

    /**
     * Return $method, $query, $server from $server
     *
     * @param Server $server
     *
     * @return array{string, array<string, mixed>, Server}
     */
    private function parseServer(array $server): array
    {
        /** @var array{argv: array<string>} $server */
        [, $method, $uri] = $server['argv'];
        $urlQuery = parse_url($uri, PHP_URL_QUERY);
        $urlPath = (string) parse_url($uri, PHP_URL_PATH);
        $query = [];
        if ($urlQuery) {
            parse_str($urlQuery, $query);
        }

        $server = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $urlPath,
        ];

        /** @var array<string, array<mixed>|string> $query */
        return [$method, $query, $server];
    }
}
