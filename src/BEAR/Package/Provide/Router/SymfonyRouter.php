<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Exception\MethodNotAllowed;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Symfony Router
 *
 * @package BEAR.Package
 */
class SymfonyRouter implements RouterInterface
{

    /**
     * @var RequestContext
     */
    private $context;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileLocation;

    /**
     * @var RouteCollection
     */
    private $collection;

    /**
     * @var Request
     */
    private $request;

    const METHOD_OVERRIDE = 'X-HTTP-Method-Override';
    const METHOD_OVERRIDE_GET = '_method';

    /**
     * Sets the location of the routes file
     *
     * @param string $fileLocation
     *
     * @Inject
     * @Named("fileLocation=routes_file_location")
     */
    public function setFilePath($fileLocation)
    {
        $this->fileLocation = $fileLocation;
    }

    /**
     * Set globals
     *
     * @param mixed $globals array | \ArrayAccess
     *
     * @return self
     */
    public function setGlobals($globals)
    {
        $request = new Request($globals['_GET'], $globals['_POST'], [], $globals['_COOKIE'], $globals['_FILES'], $globals['_SERVER']);
        parse_str($request->getContent(), $data);
        $request->request = new ParameterBag($data);
        $context = new RequestContext;
        $context->fromRequest($request);
        $get = $request->query->all();
        $post = $request->request->all();
        $context->setParameters(array_merge($get, $post));
        $this->context = $context;
    }


    /**
     * Set argv
     *
     * @param array $argv
     *
     * @return self
     *
     * @throws BadRequest
     * @throws MethodNotAllowed
     */
    public function setArgv($argv)
    {
        if (count($argv) < 3) {
            throw new BadRequest('Usage: [get|post|put|delete] [uri]');
        }
        $context = new RequestContext;
        $context->setPathInfo(parse_url($argv[2], PHP_URL_PATH));
        $context->setMethod(strtoupper($argv[1]));
        parse_str(parse_url($argv[2], PHP_URL_QUERY), $params);
        $context->setParameters($params);
        $this->context = $context;
        return $this;
    }

    /**
     * Sets routing collection
     */
    public function setCollection(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Sets context
     *
     * @param \Symfony\Component\Routing\RequestContext $context
     */
    public function setContext(RequestContext $context = null)
    {
        if ($this->context && !$context) {
            return;
        }
        if (!$context) {
            $context = new RequestContext();
            $request = Request::createFromGlobals();
            $context->fromRequest($request);
            $get = $request->query->all();
            $post = $request->request->all();
            $context->setParameters(array_merge($get, $post));
        }
        $this->context = $context;
    }

    /**
     * Match route
     *
     * @return array [$method, $pageUri, $query]
     */
    public function match()
    {
        $this->setContext();
        $this->loadSymfonyRouteFiles();
        $this->overrideMethods();

        $matcher = new UrlMatcher($this->collection, $this->context);
        $query = $this->context->getParameters();

        try {
            $match = $matcher->match($this->context->getPathInfo());
            $pageUri = $match['_path'];
            unset($match['_path'], $match['_route']);
            $query = array_merge($query, $match);
        } catch (\Exception $e) {
            $pageUri = $this->context->getPathInfo();
        }

        $method = strtolower($this->context->getMethod());
        unset($query[self::METHOD_OVERRIDE]);

        return [$method, $pageUri, $query];
    }

    /**
     * Loads Symfony Yml Files
     */
    private function loadSymfonyRouteFiles()
    {
        if ($this->collection) {
            return;
        }
        $locator = new FileLocator(array($this->fileLocation));
        $loader = new YamlFileLoader($locator);
        $this->collection = $loader->load($this->fileName ? : 'routes.yml');

    }

    /**
     * Allows overriding of methods using BEAR.Sunday standards
     */
    private function overrideMethods()
    {
        $params = $this->context->getParameters();
        switch ($this->context->getMethod()) {
            case 'GET':
                if (isset($params[self::METHOD_OVERRIDE_GET])) {
                    $this->context->setMethod($params[self::METHOD_OVERRIDE_GET]);
                    unset($params[self::METHOD_OVERRIDE_GET]);
                }
                break;
            case 'POST':
                if (isset($params[self::METHOD_OVERRIDE])) {
                    $this->context->setMethod($params[self::METHOD_OVERRIDE]);
                    unset($params[self::METHOD_OVERRIDE]);
                }
                break;
        }
        $this->context->setParameters($params);
    }
}
