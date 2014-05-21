<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Application;

use Aura\Di\Exception;
use BEAR\Package\Dev\Application\Exception\FileAlreadyExists;
use BEAR\Package\Dev\Application\Exception\InvalidUri;
use BEAR\Package\Dev\Application\Exception\NotWritable;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Application reflector
 */
class ApplicationReflector
{
    /**
     * @var \BEAR\Package\Provide\Application\AbstractApp
     */
    public $app;

    /**
     * @var string
     */
    public $appName;

    /**
     * @var string
     */
    public $appDir;

    /**
     * @param AppInterface $app
     */
    public function __construct(AppInterface $app)
    {
        $this->app = $app;
        $ref = new \ReflectionClass($app);
        $this->appName = $ref->getNamespaceName();
        $this->appDir = dirname($ref->getFileName());
    }

    /**
     * Return resource list
     *
     * @return array
     */
    public function getResources()
    {
        $resources = $this->getResourcesUris();
        $list = [];
        foreach ($resources as $uri) {
            try {
                $response = $this->app->resource->options->uri($uri)->eager->request();
                $list[$uri] = [
                    'class' => get_class($response),
                    'options' => $response->headers,
                    'links' => $response->links
                ];
            } catch (\Exception $e) {
            }
        }

        return $list;
    }

    /**
     * @return array resource uri list
     */
    private function getResourcesUris()
    {
        $resources = [];
        $resourceDir = $this->appDir . '/Resource';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resourceDir), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            /** @var $item \SplFileInfo */
            if ($item->isFile() && $item->getExtension() === 'php' && strpos($item->getBasename('.php'), '.') === false) {
                $uri = $this->getUri($item, $resourceDir);
                if ($uri) {
                    $resources[] = $uri;
                }
            }
        }

        return $resources;
    }

    /**
     * @param \SplFileInfo $file
     * @param string       $resourceDir
     *
     * @return string
     */
    private function getUri(\SplFileInfo $file, $resourceDir)
    {
        $relativePath = strtolower(str_replace($resourceDir . '/', '', (string) $file));
        $path = explode('/', $relativePath);
        $scheme = array_shift($path);
        $appName = 'self';
        $uri = "{$scheme}://{$appName}/" . implode('/', $path);
        $uri = substr($uri, 0, -4);

        return $uri;
    }

    /**
     * Create new resource
     *
     * @param string $uri
     *
     * @return int
     */
    public function newResource($uri)
    {
        list($filePath, $fileContents) = $this->getNewResource($uri);
        $bytes = $this->filePutContents($filePath, $fileContents);

        return [$bytes, $filePath];
    }

    /**
     * @param string $uri
     *
     * @return array      [$filePath, $fileContents]
     * @throws InvalidUri
     */
    public function getNewResource($uri)
    {
        $url = parse_url($uri);
        if (!(isset($url['scheme']) && isset($url['host']))) {
            throw new InvalidUri($uri, 400);
        }
        $path = $url['path'];
        $path = implode('/', array_map('ucwords', explode('/', $path)));
        $path = str_replace('//', '/', $path);
        // cut head /
        $filePath = $this->appDir . '/Resource/' . ucwords($url['scheme']) . $path . '.php';
        $fileContents = file_get_contents(__DIR__ . '/resource.tpl');
        $fileContents = str_replace('{$app}', $this->appName, $fileContents);
        $paths = explode('/', $this->appName . $path);
        $class = array_pop($paths);
        $appName = array_shift($paths);
        $scheme = ucwords($url['scheme']);
        $namespace = "{$appName}\\Resource\\{$scheme}";
        if (count($paths) > 0) {
            $namespace .= '\\' . implode('\\', $paths);
        }
        $fileContents = str_replace('{$namespace}', $namespace, $fileContents);
        $fileContents = str_replace('{$class}', $class, $fileContents);

        return [$filePath, $fileContents];
    }

    /**
     * @param string $path
     * @param string $contents
     *
     * @return int               size of file
     * @throws NotWritable
     * @throws FileAlreadyExists
     */
    public function filePutContents($path, $contents)
    {
        $parts = explode('/', $path);
        $file = array_pop($parts);
        $dir = '';
        foreach ($parts as $part) {
            if (!is_dir($dir .= "/$part")) {
                if (!is_writable(dirname($dir))) {
                    throw new NotWritable($dir, 500);
                }
                mkdir($dir);
            }
        }
        $file = "$dir/$file";
        if (file_exists($file)) {
            throw new FileAlreadyExists("File already exits [{$file}]", 500);
        }
        $result = file_put_contents($file, $contents);

        return $result;
    }

    /**
     * @param ResourceObject $ro
     *
     * @return array
     */
    public function getResourceOptions(ResourceObject $ro)
    {
        $ref = new \ReflectionClass($ro);
        $methods = $ref->getMethods();
        $allow = [];
        foreach ($methods as $method) {
            $isRequestMethod = (substr($method->name, 0, 2) === 'on') && (substr($method->name, 0, 6) !== 'onLink');
            if ($isRequestMethod) {
                $allow[] = strtolower(substr($method->name, 2));
            }
        }
        $params = [];
        foreach ($allow as $method) {
            $refMethod = new \ReflectionMethod($ro, 'on' . $method);
            $parameters = $refMethod->getParameters();
            $paramArray = [];
            foreach ($parameters as $parameter) {
                $name = $parameter->getName();
                $param = $parameter->isOptional() ? "({$name})" : $name;
                $paramArray[] = $param;
            }
            $key = "param-{$method}";
            $params[$key] = implode(',', $paramArray);
        }
        $result = ['allow' => $allow, 'params' => $params];

        return $result;
    }

    /**
     * Compile all resources
     *
     * @return int the number of cached resource
     */
    public function compileAllResources()
    {
        // this call make all resources cached when resource client has cache.
        $cachedUriCount = count($this->getResources());

        return $cachedUriCount;
    }
}
