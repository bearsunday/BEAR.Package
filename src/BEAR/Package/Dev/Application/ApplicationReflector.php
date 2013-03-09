<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Application;

use BEAR\Sunday\Extension\Application\AppInterface;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use BEAR\Resource\AbstractObject as ResourceObject;

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
            } catch (\BEAR\Resource\Exception\ResourceNotFound $e) {
            }
        }

        return $list;
    }

    /**
     * @param $uri
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getNewResource($uri)
    {
        $url = parse_url($uri);
        if (!(isset($url['scheme']) && isset($url['host']))) {
            throw new \RuntimeException("Invalid URI [$uri]", 400);
        }
        $path = implode('/', array_map('ucwords', explode('/', $url['path'])));
        $filePath = $this->appDir . '/' . 'Resource/' . ucwords($url['scheme']) . $path . '.php';
        $fileContents = file_get_contents(__DIR__ . '/resource.tpl');
        $fileContents = str_replace('{$app}', $this->appName, $fileContents);
        $array = explode('/', $this->appName . $path);
        $class = array_pop($array);
        $appName = array_shift($array);
        $scheme = ucwords($url['scheme']);
        $namespace = "{$appName}\\Resource\\{$scheme}";
        if (count($array) > 0) {
            $namespace .=  '\\' . implode('\\', $array);
        }
        $fileContents = str_replace('{$namespace}', $namespace, $fileContents);
        $fileContents = str_replace('{$class}', $class, $fileContents);
        return [$filePath, $fileContents];

    }

    /**
     * @param $path
     * @param $contents
     *
     * @throws \RuntimeException
     */
    public function filePutContents($path, $contents)
    {
        $parts = explode('/', $path);
        $file = array_pop($parts);
        $dir = '';
        foreach ($parts as $part) {
            if (!is_dir($dir .= "/$part")) {
                mkdir($dir);
                if (!is_writable($dir)) {
                    throw new \RuntimeException("Not writable dir [$dir]", 500);
                }
            }
        }
        $file = "$dir/$file";
        if (file_exists($file)) {
            throw new \RuntimeException("File already exits file", 500);
        }
        file_put_contents($file, $contents);
        if (!is_writable($file)) {
            throw new \RuntimeException("Not writable file [$file]", 500);
        }
    }

    /**
     * @param \BEAR\Resource\AbstractObject $ro
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
     * @return array resource uri list
     */
    private function getResourcesUris()
    {
        $resources = [];
        $resourceDir = $this->appDir . '/Resource';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resourceDir), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            /** @var $item \SplFileInfo */
            if ($item->isFile() && substr($item->getFilename(), -3) === 'php') {
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
        $relativePath = strtolower(str_replace($resourceDir . '/', '', (string)$file));
        $path = explode('/', $relativePath);
        $scheme = array_shift($path);
        $appName = 'self';
        $uri = "{$scheme}://{$appName}/" . implode('/', $path);
        $uri = substr($uri, 0, -4);

        return $uri;
    }
}
