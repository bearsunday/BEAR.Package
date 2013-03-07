<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */
namespace Doctrine\Common\Cache;

/**
 * Interface for cache drivers.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
interface Cache
{
    const STATS_HITS = 'hits';
    const STATS_MISSES = 'misses';
    const STATS_UPTIME = 'uptime';
    const STATS_MEMORY_USAGE = 'memory_usage';
    const STATS_MEMORY_AVAILIABLE = 'memory_available';
    /**
     * Fetches an entry from the cache.
     *
     * @param string $id cache id The id of the cache entry to fetch.
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function fetch($id);
    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id cache id The cache id of the entry to check for.
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    public function contains($id);
    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param mixed $data The cache entry/data.
     * @param int $lifeTime The lifetime. If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function save($id, $data, $lifeTime = 0);
    /**
     * Deletes a cache entry.
     *
     * @param string $id cache id
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    public function delete($id);
    /**
     * Retrieves cached information from data store
     *
     * The server's statistics array has the following values:
     *
     * - <b>hits</b>
     * Number of keys that have been requested and found present.
     *
     * - <b>misses</b>
     * Number of items that have been requested and not found.
     *
     * - <b>uptime</b>
     * Time that the server is running.
     *
     * - <b>memory_usage</b>
     * Memory used by this server to store items.
     *
     * - <b>memory_available</b>
     * Memory allowed to use for storage.
     *
     * @since   2.2
     * @var     array Associative array with server's statistics if available, NULL otherwise.
     */
    public function getStats();
}
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */
namespace Doctrine\Common\Cache;

/**
 * Base class for cache provider implementations.
 *
 * @since   2.2
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
abstract class CacheProvider implements Cache
{
    const DOCTRINE_NAMESPACE_CACHEKEY = 'DoctrineNamespaceCacheKey[%s]';
    /**
     * @var string The namespace to prefix all cache ids with
     */
    private $namespace = '';
    /**
     * @var string The namespace version
     */
    private $namespaceVersion;
    /**
     * Set the namespace to prefix all cache ids with.
     *
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace)
    {
        $this->namespace = (string) $namespace;
    }
    /**
     * Retrieve the namespace that prefixes all cache ids.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }
    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        return $this->doFetch($this->getNamespacedId($id));
    }
    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->doContains($this->getNamespacedId($id));
    }
    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->doSave($this->getNamespacedId($id), $data, $lifeTime);
    }
    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->doDelete($this->getNamespacedId($id));
    }
    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return $this->doGetStats();
    }
    /**
     * Deletes all cache entries.
     *
     * @return boolean TRUE if the cache entries were successfully flushed, FALSE otherwise.
     */
    public function flushAll()
    {
        return $this->doFlush();
    }
    /**
     * Delete all cache entries.
     *
     * @return boolean TRUE if the cache entries were successfully deleted, FALSE otherwise.
     */
    public function deleteAll()
    {
        $namespaceCacheKey = $this->getNamespaceCacheKey();
        $namespaceVersion = $this->getNamespaceVersion() + 1;
        $this->namespaceVersion = $namespaceVersion;
        return $this->doSave($namespaceCacheKey, $namespaceVersion);
    }
    /**
     * Prefix the passed id with the configured namespace value
     *
     * @param string $id  The id to namespace
     * @return string $id The namespaced id
     */
    private function getNamespacedId($id)
    {
        $namespaceVersion = $this->getNamespaceVersion();
        return sprintf('%s[%s][%s]', $this->namespace, $id, $namespaceVersion);
    }
    /**
     * Namespace cache key
     *
     * @return string $namespaceCacheKey
     */
    private function getNamespaceCacheKey()
    {
        return sprintf(self::DOCTRINE_NAMESPACE_CACHEKEY, $this->namespace);
    }
    /**
     * Namespace version
     *
     * @return string $namespaceVersion
     */
    private function getNamespaceVersion()
    {
        if (null !== $this->namespaceVersion) {
            return $this->namespaceVersion;
        }
        $namespaceCacheKey = $this->getNamespaceCacheKey();
        $namespaceVersion = $this->doFetch($namespaceCacheKey);
        if (false === $namespaceVersion) {
            $namespaceVersion = 1;
            $this->doSave($namespaceCacheKey, $namespaceVersion);
        }
        $this->namespaceVersion = $namespaceVersion;
        return $this->namespaceVersion;
    }
    /**
     * Fetches an entry from the cache.
     *
     * @param string $id cache id The id of the cache entry to fetch.
     * @return string The cached data or FALSE, if no cache entry exists for the given id.
     */
    protected abstract function doFetch($id);
    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id cache id The cache id of the entry to check for.
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    protected abstract function doContains($id);
    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param string $data The cache entry/data.
     * @param bool|int $lifeTime The lifetime. If != false, sets a specific lifetime for this
     *                           cache entry (null => infinite lifeTime).
     *
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    protected abstract function doSave($id, $data, $lifeTime = false);
    /**
     * Deletes a cache entry.
     *
     * @param string $id cache id
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    protected abstract function doDelete($id);
    /**
     * Deletes all cache entries.
     *
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    protected abstract function doFlush();
    /**
     * Retrieves cached information from data store
     *
     * @since   2.2
     * @return  array An associative array with server's statistics if available, NULL otherwise.
     */
    protected abstract function doGetStats();
}
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */
namespace Doctrine\Common\Cache;

/**
 * APC cache provider.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  David Abdemoulaie <dave@hobodave.com>
 */
class ApcCache extends CacheProvider
{
    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return apc_fetch($id);
    }
    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return apc_exists($id);
    }
    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return (bool) apc_store($id, $data, (int) $lifeTime);
    }
    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return apc_delete($id);
    }
    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return apc_clear_cache() && apc_clear_cache('user');
    }
    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $info = apc_cache_info();
        $sma = apc_sma_info();
        return array(Cache::STATS_HITS => $info['num_hits'], Cache::STATS_MISSES => $info['num_misses'], Cache::STATS_UPTIME => $info['start_time'], Cache::STATS_MEMORY_USAGE => $info['mem_size'], Cache::STATS_MEMORY_AVAILIABLE => $sma['avail_mem']);
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Application;

use Ray\Di\Injector;
use Aura\Di\Exception;
use BEAR\Package\Provide\Application\DiLogger;
use Doctrine\Common\Cache\Cache;
use Ray\Di\AbstractModule;
use Ray\Di\Container;
use Ray\Di\Forge;
use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use BEAR\Package\Provide\Application\Exception\InvalidMode;
use Ray\Di\Exception\Exception as DiException;
/**
 * Application object factory
 */
class ApplicationFactory
{
    /**
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
    /**
     * Return application instance
     *
     * @param string $appName application name
     * @param string $mode    run mode
     *
     * @return \BEAR\Sunday\Extension\Application\AppInterface
     * @throws InvalidMode
     */
    public function newInstance($appName, $mode)
    {
        $appKey = PHP_SAPI . $appName . $mode;
        $app = $this->cache->fetch($appKey);
        if ($app) {
            return $app;
        }
        $moduleName = $appName . '\\Module\\' . $mode . 'Module';
        if (!class_exists($moduleName)) {
            throw new InvalidMode("Invalid mode [{$mode}], [{$moduleName}] class unavailable");
        }
        $injector = (new Injector(new Container(new Forge(new Config(new Annotation(new Definition(), new CachedReader(new AnnotationReader(), $this->cache))))), new $moduleName()))->setCache($this->cache);
        $diLogger = $injector->getInstance('BEAR\\Package\\Provide\\Application\\DiLogger');
        $injector->setLogger($diLogger);
        $app = $injector->getInstance('BEAR\\Sunday\\Extension\\Application\\AppInterface');
        /** @var $app \BEAR\Sunday\Extension\Application\AppInterface */
        $this->cache->save($appKey, $app);
        // log
        try {
            $logger = $injector->getInstance('Guzzle\\Log\\LogAdapterInterface');
            /** @var $logger \Guzzle\Log\LogAdapterInterface */
            $logger->log((string) $diLogger, LOG_INFO);
        } catch (DiException $e) {
            error_log((string) $diLogger, LOG_INFO);
        }
        return $app;
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use ArrayObject;
use ArrayAccess;
/**
 * A module contributes configuration information, typically interface bindings,
 *  which will be used to create an Injector.
 *
 * @package   Ray.Di
 */
abstract class AbstractModule implements ArrayAccess
{
    /**
     * Bind
     *
     * @var string
     */
    const BIND = 'bind';
    /**
     * Name
     *
     * @var string
     */
    const NAME = 'name';
    /**
     * In (Scope)
     *
     * @var string
     */
    const IN = 'in';
    /**
     * To
     *
     * @var string
     */
    const TO = 'to';
    /**
     * To Class
     *
     * @var string
     */
    const TO_CLASS = 'class';
    /**
     * Provider
     *
     * @var string
     */
    const TO_PROVIDER = 'provider';
    /**
     * To Instance
     *
     * @var string
     */
    const TO_INSTANCE = 'instance';
    /**
     * To Closure
     *
     * @var string
     */
    const TO_CALLABLE = 'callable';
    /**
     * To Constructor
     *
     * @var string
     */
    const TO_CONSTRUCTOR = 'constructor';
    /**
     * To Constructor
     *
     * @var string
     */
    const TO_SETTER = 'setter';
    /**
     * To Scope
     *
     * @var string
     */
    const SCOPE = 'scope';
    /**
     * Unspecified name
     *
     * @var string
     */
    const NAME_UNSPECIFIED = '*';
    /**
     * Binding definition
     *
     * @var Definition
     */
    public $bindings;
    /**
     * Pointcuts
     *
     * @var ArrayObject
     */
    /**
     * Current Binding
     *
     * @var string
     */
    protected $currentBinding;
    /**
     * Current Name
     *
     * @var string
     */
    protected $currentName = self::NAME_UNSPECIFIED;
    /**
     * Scope
     *
     * @var array
     */
    protected $scope = array(Scope::PROTOTYPE, Scope::SINGLETON);
    /**
     * Pointcuts
     *
     * @var array
     */
    public $pointcuts = array();
    /**
     * @var InjectorInterface
     */
    protected $dependencyInjector;
    /**
     * Is activated
     *
     * @var bool
     */
    protected $activated = false;
    /**
     * Installed modules
     *
     * @var array
     */
    public $modules = array();
    /**
     * Constructor
     *
     * @param AbstractModule $module
     * @param Matcher        $matcher
     */
    public function __construct(AbstractModule $module = null, Matcher $matcher = null)
    {
        if (is_null($module)) {
            $this->bindings = new ArrayObject();
            $this->pointcuts = new ArrayObject();
        } else {
            $module->activate();
            $this->bindings = $module->bindings;
            $this->pointcuts = $module->pointcuts;
        }
        $this->modules[] = get_class($this);
        $this->matcher = $matcher ?: new Matcher(new Reader());
    }
    /**
     * Activation
     *
     * @param InjectorInterface $injector
     */
    public function activate(InjectorInterface $injector = null)
    {
        if ($this->activated === true) {
            return;
        }
        $this->activated = true;
        $this->dependencyInjector = $injector ?: Injector::create(array($this));
        $this->configure();
    }
    /**
     * Configures a Binder via the exposed methods.
     *
     * @return void
     */
    protected abstract function configure();
    /**
     * Set bind interface
     *
     * @param string $interface
     *
     * @return AbstractModule
     */
    protected function bind($interface = '')
    {
        if (strlen($interface) > 0 && $interface[0] === '\\') {
            // remove leading back slash
            $interface = substr($interface, 1);
        }
        $this->currentBinding = $interface;
        $this->currentName = self::NAME_UNSPECIFIED;
        return $this;
    }
    /**
     * Set binding annotation.
     *
     * @param string $name
     *
     * @return AbstractModule
     */
    protected function annotatedWith($name)
    {
        $this->currentName = $name;
        $this->bindings[$this->currentBinding][$name] = array(self::IN => Scope::SINGLETON);
        return $this;
    }
    /**
     * Set scope
     *
     * @param string $scope
     *
     * @return AbstractModule
     */
    protected function in($scope)
    {
        $this->bindings[$this->currentBinding][$this->currentName][self::IN] = $scope;
        return $this;
    }
    /**
     * To class
     *
     * @param string $class
     *
     * @return AbstractModule
     * @throws Exception\ToBinding
     */
    protected function to($class)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = array(self::TO => array(self::TO_CLASS, $class));
        return $this;
    }
    /**
     * To provider
     *
     * @param string $provider provider class
     *
     * @return AbstractModule
     * @throws Exception\Configuration
     */
    protected function toProvider($provider)
    {
        $hasProviderInterface = class_exists($provider) && in_array('Ray\\Di\\ProviderInterface', class_implements($provider));
        if ($hasProviderInterface === false) {
            throw new Exception\Configuration($provider);
        }
        $this->bindings[$this->currentBinding][$this->currentName] = array(self::TO => array(self::TO_PROVIDER, $provider));
        return $this;
    }
    /**
     * To instance
     *
     * @param mixed $instance
     *
     * @return AbstractModule
     */
    protected function toInstance($instance)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = array(self::TO => array(self::TO_INSTANCE, $instance));
    }
    /**
     * To closure
     *
     * @param Callable $callable
     *
     * @return void
     */
    protected function toCallable(callable $callable)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = array(self::TO => array(self::TO_CALLABLE, $callable));
    }
    /**
     * To constructor
     *
     * @param array $params
     */
    protected function toConstructor(array $params)
    {
        $this->bindings[$this->currentBinding][$this->currentName] = array(self::TO => array(self::TO_CONSTRUCTOR, $params));
    }
    /**
     * Bind interceptor
     *
     * @param Matcher $classMatcher
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     *
     * @return void
     */
    protected function bindInterceptor(Matcher $classMatcher, Matcher $methodMatcher, array $interceptors)
    {
        $id = uniqid();
        $this->pointcuts[$id] = new Pointcut($classMatcher, $methodMatcher, $interceptors);
    }
    /**
     * Install module
     *
     * @param AbstractModule $module
     */
    public function install(AbstractModule $module)
    {
        $module->activate($this->dependencyInjector);
        $this->pointcuts = new ArrayObject(array_merge((array) $module->pointcuts, (array) $this->pointcuts));
        $this->bindings = new ArrayObject(array_merge_recursive((array) $this->bindings, (array) $module->bindings));
        if ($module->modules) {
            $this->modules = array_merge($this->modules, array(), $module->modules);
        }
    }
    /**
     * Request injection
     *
     * Get instance with current module.
     *
     * @param string $class
     *
     * @return object
     */
    public function requestInjection($class)
    {
        $module = $this->dependencyInjector->getModule();
        $this->dependencyInjector->setModule($this, false);
        $instance = $this->dependencyInjector->getInstance($class);
        if ($module instanceof AbstractModule) {
            $this->dependencyInjector->setModule($module, false);
        }
        return $instance;
    }
    /**
     * Return matched binder
     *
     * @param string $class
     * @param Bind   $bind
     *
     * @return Bind $bind
     */
    public function __invoke($class, Bind $bind)
    {
        $bind->bind($class, (array) $this->pointcuts);
        return $bind;
    }
    /**
     * ArrayAccess::offsetExists
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->bindings[$offset]);
    }
    /**
     * ArrayAccess::offsetGet
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->bindings[$offset]) ? $this->bindings[$offset] : null;
    }
    /**
     * ArrayAccess::offsetSet
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @throws Exception\ReadOnly
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception\ReadOnly();
    }
    /**
     * ArrayAccess::offsetUnset
     *
     * @param string $offset
     *
     * @throws Exception\ReadOnly
     */
    public function offsetUnset($offset)
    {
        throw new Exception\ReadOnly();
    }
    /**
     * Return binding information
     *
     * @return string
     */
    public function __toString()
    {
        $output = '';
        foreach ((array) $this->bindings as $bind => $bindTo) {
            foreach ($bindTo as $annotate => $to) {
                $type = $to['to'][0];
                $output .= $annotate !== '*' ? "bind('{$bind}')->annotatedWith('{$annotate}')" : "bind('{$bind}')";
                if ($type === 'class') {
                    $output .= '->to(\'' . $to['to'][1] . '\')';
                }
                if ($type === 'instance') {
                    $instance = $to['to'][1];
                    $type = gettype($instance);
                    switch ($type) {
                        case 'object':
                            $instance = '(object) ' . get_class($instance);
                            break;
                        case 'array':
                            $instance = '(array) ' . json_encode($instance);
                            break;
                        case 'string':
                            $instance = "'{$instance}'";
                            break;
                        case 'boolean':
                            $instance = '(bool) ' . ($instance ? 'true' : 'false');
                            break;
                        default:
                            $instance = "({$type}) {$instance}";
                    }
                    $output .= '->toInstance(' . $instance . ')';
                }
                if ($type === 'provider') {
                    $provider = $to['to'][1];
                    $output .= '->toProvider(\'' . $provider . '\')';
                }
                $output .= PHP_EOL;
            }
        }
        return $output;
    }
    /**
     * Keep only bindings and pointcuts.
     *
     * @return array
     */
    public function __sleep()
    {
        return array('bindings', 'pointcuts');
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Cache\Cache;
/**
 * Defines the interface for dependency injector.
 *
 * @package Ray.Di
 *
 */
interface InjectorInterface
{
    /**
     * Creates and returns a new instance of a class using 'module,
     * optionally with overriding params.
     *
     * @param string         $class  The class to instantiate.
     * @param array          $params An associative array of override parameters where
     *                               the key the name of the constructor parameter and the value is the
     *                               parameter value to use.
     *
     * @return object
     */
    public function getInstance($class, array $params = null);
    /**
     * Return container
     *
     * @return Container;
     */
    public function getContainer();
    /**
     * Return module
     *
     * @return AbstractModule
     */
    public function getModule();
    /**
     * Set Logger
     *
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger);
    /**
     * Get Logger
     *
     * @return LoggerInterface
     */
    public function getLogger();
    /**
     * Set module
     *
     * @param AbstractModule $module
     *
     * @return self
     */
    public function setModule(AbstractModule $module);
    /**
     * Set cache adapter
     *
     * @param Cache $cache
     *
     * @return self
     */
    public function setCache(Cache $cache);
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\Cache;
use Ray\Di\Exception;
use LogicException;
use Ray\Di\Exception\OptionalInjectionNotBound;
use Ray\Di\Exception\Binding;
use Ray\Aop\BindInterface;
use Ray\Aop\Bind;
use Ray\Aop\Weaver;
use Aura\Di\Lazy;
use Aura\Di\ContainerInterface;
use Aura\Di\Exception\ContainerLocked;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use SplObjectStorage;
/**
 * Dependency Injector
 *
 * @package Ray.Di
 */
class Injector implements InjectorInterface
{
    /**
     * Inject annotation with optional=false
     *
     * @var bool
     */
    const OPTIONAL_BINDING_NOT_BOUND = false;
    /**
     * Config
     *
     * @var Config
     */
    protected $config;
    /**
     * Container
     *
     * @var \Ray\Di\Container
     */
    protected $container;
    /**
     * Binding module
     *
     * @var AbstractModule
     */
    protected $module;
    /**
     * Pre-destroy objects
     *
     * @var SplObjectStorage
     */
    private $preDestroyObjects;
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $log;
    /**
     * Current working class for exception message
     *
     * @var string
     */
    private $class;
    /**
     * Cache adapter
     *
     * @var Cache
     */
    private $cache;
    /**
     * Set binding module
     *
     * @param AbstractModule $module
     * @param bool           $activate
     *
     * @return self
     * @throws \Aura\Di\Exception\ContainerLocked
     */
    public function setModule(AbstractModule $module, $activate = true)
    {
        if ($this->container->isLocked()) {
            throw new ContainerLocked();
        }
        if ($activate === true) {
            $module->activate($this);
        }
        $this->module = $module;
        return $this;
    }
    /**
     * Return module
     *
     * @return AbstractModule
     */
    public function getModule()
    {
        return $this->module;
    }
    /**
     * Set Logger
     *
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->log = $logger;
        return $this;
    }
    /**
     * Get Logger
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->log;
    }
    /**
     * Return container
     *
     * @return \Aura\Di\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
    /**
     * (non-PHPDoc)
     * @see \Ray\Di\InjectorInterface::setCache()
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }
    /**
     * Constructor
     *
     * @param ContainerInterface $container The class to instantiate.
     * @param AbstractModule     $module    Binding configuration module
     * @param BindInterface      $bind      Aspect binder
     */
    public function __construct(ContainerInterface $container, AbstractModule $module = null, BindInterface $bind = null)
    {
        $this->container = $container;
        $this->module = $module ?: new EmptyModule();
        $this->bind = $bind ?: new Bind();
        $this->preDestroyObjects = new SplObjectStorage();
        $this->config = $container->getForge()->getConfig();
        $this->module->activate($this);
    }
    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->notifyPreShutdown();
    }
    /**
     * Clone
     */
    public function __clone()
    {
        $this->container = clone $this->container;
    }
    /**
     * Injector builder
     *
     * @param       array AbstractModule[] $modules
     * @param Cache $cache
     *
     * @return Injector
     */
    public static function create(array $modules = array(), Cache $cache = null)
    {
        if (is_null($cache)) {
            $injector = new self(new Container(new Forge(new Config(new Annotation(new Definition(), new AnnotationReader())))));
        } else {
            $injector = new self(new Container(new Forge(new Config(new Annotation(new Definition(), new CachedReader(new AnnotationReader(), $cache))))));
            $injector->setCache($cache);
        }
        if (count($modules) > 0) {
            $module = array_shift($modules);
            foreach ($modules as $extraModule) {
                /* @var $module AbstractModule */
                $module->install($extraModule);
            }
            $injector->setModule($module);
        }
        return $injector;
    }
    /**
     * Get a service object using binding module, optionally with overriding params.
     *
     * @param string $class  The class or interface to instantiate.
     * @param array  $params An associative array of override parameters where
     *                       the key the name of the constructor parameter and the value is the
     *                       parameter value to use.
     *
     * @return object
     * @throws Exception\NotReadable
     */
    /**
     * (non-PHPDoc)
     * @see \Ray\Di\InjectorInterface::getInstance()
     *
     * @throws Exception\NotReadable
     */
    public function getInstance($class, array $params = null)
    {
        static $loaded = array();
        $bound = $this->getBound($class);
        // return singleton bound object if exists
        if (is_object($bound)) {
            return $bound;
        }
        $isNotRecursive = debug_backtrace()[0]['file'] !== '/Users/kooriyama/git/BEAR.Package/vendor/ray/di/src/Ray/Di/Injector.php';
        $isFirstLoadInThisSession = !in_array($class, $loaded);
        $useCache = $this->cache instanceof Cache && $isNotRecursive && $isFirstLoadInThisSession;
        $loaded[] = $class;
        // cache read ?
        if ($useCache) {
            $cacheKey = PHP_SAPI . get_class($this->module) . $class;
            $object = $this->cache->fetch($cacheKey);
            if ($object) {
                return $object;
            }
        }
        // get bound config
        list($class, $isSingleton, $interfaceClass, $config, $setter, $definition) = $bound;
        // override construction parameter
        $params = is_null($params) ? $config : array_merge($config, (array) $params);
        // lazy-load params as needed
        foreach ($params as $key => $val) {
            if ($params[$key] instanceof Lazy) {
                $params[$key] = $params[$key]();
            }
        }
        // be all parameters ready
        $this->constructorInject($class, $params, $this->module);
        // is instantiable ?
        if (!(new \ReflectionClass($class))->isInstantiable()) {
            throw new Exception\NotInstantiable($class);
        }
        // create the new instance
        $object = call_user_func_array(array($this->config->getReflect($class), 'newInstance'), $params);
        // call setter methods
        foreach ($setter as $method => $value) {
            // does the specified setter method exist?
            if (method_exists($object, $method)) {
                if (!is_array($value)) {
                    // call the setter
                    $object->{$method}($value);
                } else {
                    call_user_func_array(array($object, $method), $value);
                }
            }
        }
        // weave aspect
        $module = $this->module;
        $bind = $module($class, new $this->bind());
        /* @var $bind \Ray\Aop\Bind */
        if ($bind->hasBinding() === true) {
            $object = new Weaver($object, $bind);
        }
        // log inject info
        if ($this->log) {
            $this->log->log($class, $params, $setter, $object, $bind);
        }
        // set life cycle
        if ($definition) {
            $this->setLifeCycle($object, $definition);
        }
        // set singleton object
        if ($isSingleton) {
            $this->container->set($interfaceClass, $object);
        }
        if ($useCache) {
            /** @noinspection PhpUndefinedVariableInspection */
            $this->cache->save($cacheKey, $object);
        }
        return $object;
    }
    /**
     * Return bound object or inject info
     *
     * @param $class
     *
     * @return array|object
     * @throws Exception\NotReadable
     */
    private function getBound($class)
    {
        $class = $this->removeLeadingBackSlash($class);
        // is interface ?
        try {
            $isInterface = (new ReflectionClass($class))->isInterface();
        } catch (ReflectionException $e) {
            throw new Exception\NotReadable($class);
        }
        list($config, $setter, $definition) = $this->config->fetch($class);
        $interfaceClass = $isSingleton = false;
        if ($isInterface) {
            $bound = $this->getBoundClass($this->module->bindings, $definition, $class);
            if (is_object($bound)) {
                return $bound;
            }
            list($class, $isSingleton, $interfaceClass) = $bound;
            list($config, $setter, $definition) = $this->config->fetch($class);
        }
        $hasDirectBinding = isset($this->module->bindings[$class]);
        /** @var $definition Definition */
        if ($definition->hasDefinition() || $hasDirectBinding) {
            list($config, $setter) = $this->bindModule($setter, $definition);
        }
        return array($class, $isSingleton, $interfaceClass, $config, $setter, $definition);
    }
    /**
     * Lock
     *
     * Lock the Container so that configuration cannot be accessed externally,
     * and no new service definitions can be added.
     *
     * @return void
     */
    public function lock()
    {
        $this->container->lock();
    }
    /**
     * Lazy new
     *
     * Returns a Lazy that creates a new instance. This allows you to replace
     * the following idiom:
     *
     * @param string $class  The type of class of instantiate.
     * @param array  $params Override parameters for the instance.
     *
     * @return Lazy A lazy-load object that creates the new instance.
     */
    public function lazyNew($class, array $params = array())
    {
        return $this->container->lazyNew($class, $params);
    }
    /**
     * Remove leading back slash
     *
     * @param string $class
     *
     * @return string
     */
    private function removeLeadingBackSlash($class)
    {
        $isLeadingBackSlash = strlen($class) > 0 && $class[0] === '\\';
        if ($isLeadingBackSlash === true) {
            $class = substr($class, 1);
        }
        return $class;
    }
    /**
     * Get bound class or object
     *
     * @param        $bindings
     * @param mixed  $definition
     * @param string $class
     *
     * @return array|object
     * @throws Exception\NotBound
     */
    private function getBoundClass($bindings, $definition, $class)
    {
        if (!isset($bindings[$class]) || !isset($bindings[$class]['*']['to'][0])) {
            $msg = "Interface \"{$class}\" is not bound.";
            throw new Exception\NotBound($msg);
        }
        $toType = $bindings[$class]['*']['to'][0];
        $isToProviderBinding = $toType === AbstractModule::TO_PROVIDER;
        if ($isToProviderBinding) {
            $provider = $bindings[$class]['*']['to'][1];
            return $this->getInstance($provider)->get();
        }
        $inType = isset($bindings[$class]['*'][AbstractModule::IN]) ? $bindings[$class]['*'][AbstractModule::IN] : null;
        $inType = is_array($inType) ? $inType[0] : $inType;
        $isSingleton = $inType === Scope::SINGLETON || $definition['Scope'] == Scope::SINGLETON;
        $interfaceClass = $class;
        if ($isSingleton && $this->container->has($interfaceClass)) {
            $object = $this->container->get($interfaceClass);
            return $object;
        }
        $class = $toType === AbstractModule::TO_CLASS ? $bindings[$class]['*']['to'][1] : $class;
        return array($class, $isSingleton, $interfaceClass);
    }
    /**
     * Return parameter using TO_CONSTRUCTOR
     *
     * 1) If parameter is provided, return. (check)
     * 2) If parameter is NOT provided and TO_CONSTRUCTOR binding is available, return parameter with it
     * 3) No binding found, throw exception.
     *
     * @param string         $class
     * @param array          &$params
     * @param AbstractModule $module
     *
     * @return void
     * @throws Exception\NotBound
     */
    private function constructorInject($class, array &$params, AbstractModule $module)
    {
        $ref = method_exists($class, '__construct') ? new ReflectionMethod($class, '__construct') : false;
        if ($ref === false) {
            return;
        }
        $parameters = $ref->getParameters();
        foreach ($parameters as $index => $parameter) {
            /* @var $parameter \ReflectionParameter */
            // has binding ?
            $params = array_values($params);
            if (!isset($params[$index])) {
                $hasConstructorBinding = $module[$class]['*'][AbstractModule::TO][0] === AbstractModule::TO_CONSTRUCTOR;
                if ($hasConstructorBinding) {
                    $params[$index] = $module[$class]['*'][AbstractModule::TO][1][$parameter->name];
                    continue;
                }
                // has constructor default value ?
                if ($parameter->isDefaultValueAvailable() === true) {
                    continue;
                }
                // is typehint class ?
                $classRef = $parameter->getClass();
                if (is_null($classRef)) {
                    $msg = 'Invalid interface is not found. (array ?)';
                } elseif (!$classRef->isInterface() && $classRef) {
                    $params[$index] = $this->getInstance($classRef->getName());
                    continue;
                } else {
                    $msg = "Interface [{$classRef->name}] is not bound.";
                }
                $msg .= " Injection requested at argument #{$index} \${$parameter->name} in {$class} constructor.";
                throw new Exception\NotBound($msg);
            }
        }
    }
    /**
     * Notify pre-destroy
     *
     * @return void
     */
    private function notifyPreShutdown()
    {
        $this->preDestroyObjects->rewind();
        while ($this->preDestroyObjects->valid()) {
            $object = $this->preDestroyObjects->current();
            $method = $this->preDestroyObjects->getInfo();
            $object->{$method}();
            $this->preDestroyObjects->next();
        }
    }
    /**
     * Set object life cycle
     *
     * @param object     $instance
     * @param Definition $definition
     *
     * @return void
     */
    private function setLifeCycle($instance, Definition $definition = null)
    {
        $postConstructMethod = $definition[Definition::POST_CONSTRUCT];
        if ($postConstructMethod) {
            call_user_func(array($instance, $postConstructMethod));
        }
        if (!is_null($definition[Definition::PRE_DESTROY])) {
            $this->preDestroyObjects->attach($instance, $definition[Definition::PRE_DESTROY]);
        }
    }
    /**
     * Return dependency using modules.
     *
     * @param array          $setter
     * @param Definition     $definition
     *
     * @return array <$constructorParams, $setter>
     * @throws Exception\Binding
     * @throws \LogicException
     */
    private function bindModule(array $setter, Definition $definition)
    {
        // @return array [AbstractModule::TO => [$toMethod, $toTarget]]
        $container = $this->container;
        /* @var $forge \Ray\Di\Forge */
        $injector = $this;
        $getInstance = function ($in, $bindingToType, $target) use($container, $definition, $injector) {
            if ($in === Scope::SINGLETON && $container->has($target)) {
                $instance = $container->get($target);
                return $instance;
            }
            if ($bindingToType === AbstractModule::TO_CLASS) {
                $instance = $injector->getInstance($target);
            } else {
                //  AbstractModule::TO_PROVIDER
                $provider = $injector->getInstance($target);
                $instance = $provider->get();
            }
            if ($in === Scope::SINGLETON) {
                $container->set($target, $instance);
            }
            return $instance;
        };
        // main
        $setterDefinitions = isset($definition[Definition::INJECT][Definition::INJECT_SETTER]) ? $definition[Definition::INJECT][Definition::INJECT_SETTER] : null;
        if ($setterDefinitions !== null) {
            $injected = array();
            foreach ($setterDefinitions as $setterDefinition) {
                try {
                    $injected[] = $this->bindMethod($setterDefinition, $definition, $getInstance);
                } catch (OptionalInjectionNotBound $e) {
                    
                }
            }
            $setter = array();
            foreach ($injected as $item) {
                $setterMethod = $item[0];
                $object = count($item[1]) === 1 && $setterMethod !== '__construct' ? $item[1][0] : $item[1];
                $setter[$setterMethod] = $object;
            }
        }
        // constructor injection ?
        if (isset($setter['__construct'])) {
            $params = $setter['__construct'];
            unset($setter['__construct']);
        } else {
            $params = array();
        }
        $result = array($params, $setter);
        return $result;
    }
    /**
     * Bind method
     *
     * @param array      $setterDefinition
     * @param Definition $definition
     * @param Callable   $getInstance
     *
     * @return array
     */
    private function bindMethod(array $setterDefinition, Definition $definition, callable $getInstance)
    {
        list($method, $settings) = each($setterDefinition);
        array_walk($settings, array($this, 'bindOneParameter'), array($definition, $getInstance));
        return array($method, $settings);
    }
    /**
     * Set one parameter with definition, or JIT binding.
     *
     * @param array  &$param
     * @param string $key
     * @param array  $userData
     *
     * @return void
     * @throws Exception\OptionalInjectionNotBound
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function bindOneParameter(array &$param, $key, array $userData)
    {
        list(, $getInstance) = $userData;
        $annotate = $param[Definition::PARAM_ANNOTATE];
        $typeHint = $param[Definition::PARAM_TYPEHINT];
        $hasTypeHint = isset($this->module[$typeHint]) && isset($this->module[$typeHint][$annotate]) && $this->module[$typeHint][$annotate] !== array();
        $binding = $hasTypeHint ? $this->module[$typeHint][$annotate] : false;
        if ($binding === false || isset($binding[AbstractModule::TO]) === false) {
            // default binding by @ImplementedBy or @ProviderBy
            $binding = $this->jitBinding($param, $typeHint, $annotate);
            if ($binding === self::OPTIONAL_BINDING_NOT_BOUND) {
                throw new OptionalInjectionNotBound($key);
            }
        }
        list($bindingToType, $target) = $binding[AbstractModule::TO];
        if ($bindingToType === AbstractModule::TO_INSTANCE) {
            $param = $target;
            return;
        } elseif ($bindingToType === AbstractModule::TO_CALLABLE) {
            /* @var $target \Closure */
            $param = $target();
            return;
        }
        if (isset($binding[AbstractModule::IN])) {
            $in = $binding[AbstractModule::IN];
        } else {
            list($param, , $definition) = $this->config->fetch($typeHint);
            $in = isset($definition[Definition::SCOPE]) ? $definition[Definition::SCOPE] : Scope::PROTOTYPE;
        }
        /* @var $getInstance \Closure */
        $param = $getInstance($in, $bindingToType, $target);
    }
    /**
     * JIT binding
     *
     * @param array  $param
     * @param string $typeHint
     * @param string $annotate
     *
     * @return array|bool
     * @throws Exception\NotBound
     */
    private function jitBinding(array $param, $typeHint, $annotate)
    {
        $typeHintBy = $param[Definition::PARAM_TYPEHINT_BY];
        if ($typeHintBy == array()) {
            if ($param[Definition::OPTIONAL] === true) {
                return self::OPTIONAL_BINDING_NOT_BOUND;
            }
            $name = $param[Definition::PARAM_NAME];
            $msg = "typehint='{$typeHint}', annotate='{$annotate}' for \${$name} in class '{$this->class}'";
            $e = (new Exception\NotBound($msg))->setModule($this->module);
            throw $e;
        }
        if ($typeHintBy[0] === Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY) {
            return array(AbstractModule::TO => array(AbstractModule::TO_CLASS, $typeHintBy[1]));
        }
        return array(AbstractModule::TO => array(AbstractModule::TO_PROVIDER, $typeHintBy[1]));
    }
    /**
     * Magic get to provide access to the Config::$params and $setter
     * objects.
     *
     * @param string $key The property to retrieve ('params' or 'setter').
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->container->__get($key);
    }
    /**
     * Return module information.
     *
     * @return string
     */
    public function __toString()
    {
        $result = (string) $this->module;
        return $result;
    }
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Di
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Di;

/**
 * 
 * Interface for dependency injection containers.
 * 
 * @package Aura.Di
 * 
 */
interface ContainerInterface
{
    /**
     * 
     * Lock the Container so that configuration cannot be accessed externally,
     * and no new service definitions can be added.
     * 
     * @return void
     * 
     */
    public function lock();
    /**
     * 
     * Is the Container locked?
     * 
     * @return bool
     * 
     */
    public function isLocked();
    /**
     * 
     * Gets the Forge object used for creating new instances.
     * 
     * @return ForgeInterface
     * 
     */
    public function getForge();
    /**
     * 
     * Does a particular service exist?
     * 
     * @param string $key The service key to look up.
     * 
     * @return bool
     * 
     */
    public function has($key);
    /**
     * 
     * Sets a service object by name.
     * 
     * @param string $key The service key.
     * 
     * @param object $val The service object.
     * 
     */
    public function set($key, $val);
    /**
     * 
     * Gets a service object by key, lazy-loading it as needed.
     * 
     * @param string $key The service to get.
     * 
     * @return object
     * 
     * @throws \Aura\Di\Exception\ServiceNotFound when the requested service
     * does not exist.
     * 
     */
    public function get($key);
    /**
     * 
     * Gets the list of services provided.
     * 
     * @return array
     * 
     */
    public function getServices();
    /**
     * 
     * Gets the list of service definitions.
     * 
     * @return array
     * 
     */
    public function getDefs();
    /**
     * 
     * Returns a Lazy that gets a service.
     * 
     * @param string $key The service name; it does not need to exist yet.
     * 
     * @return Lazy A lazy-load object that gets the named service.
     * 
     */
    public function lazyGet($key);
    /**
     * 
     * Returns a new instance of the specified class, optionally
     * with additional override parameters.
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setters Override setters for the instance.
     * 
     * @return object An instance of the requested class.
     * 
     */
    public function newInstance($class, array $params = array(), array $setters = array());
    /**
     * 
     * Returns a Lazy that creates a new instance.
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setters Override setters for the instance.
     * 
     * @return Lazy A lazy-load object that creates the new instance.
     * 
     */
    public function lazyNew($class, array $params = array(), array $setters = array());
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Di
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Di;

/**
 * 
 * Dependency injection container.
 * 
 * @package Aura.Di
 * 
 */
class Container implements ContainerInterface
{
    /**
     * 
     * A Forge object to create classes through reflection.
     * 
     * @var array
     * 
     */
    protected $forge;
    /**
     * 
     * A convenient reference to the Config::$params object, which itself
     * is contained by the Forge object.
     * 
     * @var \ArrayObject
     * 
     */
    protected $params;
    /**
     * 
     * A convenient reference to the Config::$setter object, which itself
     * is contained by the Forge object.
     * 
     * @var \ArrayObject
     * 
     */
    protected $setter;
    /**
     * 
     * Retains named service definitions.
     * 
     * @var array
     * 
     */
    protected $defs = array();
    /**
     * 
     * Retains the actual service objects.
     * 
     * @var array
     * 
     */
    protected $services = array();
    /**
     * 
     * Is the Container locked?  (When locked, you cannot access configuration
     * properties from outside the object, and cannot set services.)
     * 
     * @var bool
     * 
     * @see __get()
     * 
     * @see set()
     * 
     */
    protected $locked = false;
    /**
     * 
     * Constructor.
     * 
     * @param ForgeInterface $forge A forge for creating objects using
     * keyword parameter configuration.
     * 
     */
    public function __construct(ForgeInterface $forge)
    {
        $this->forge = $forge;
        $this->params = $this->getForge()->getConfig()->getParams();
        $this->setter = $this->getForge()->getConfig()->getSetter();
    }
    /**
     * 
     * Magic get to provide access to the Config::$params and $setter
     * objects.
     * 
     * @param string $key The property to retrieve ('params' or 'setter').
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        if ($this->isLocked()) {
            throw new Exception\ContainerLocked();
        }
        if ($key == 'params' || $key == 'setter') {
            return $this->{$key};
        }
        throw new \UnexpectedValueException($key);
    }
    /**
     * 
     * When cloning this Container, *do not* make a copy of the service
     * objects.  Leave the configuration and definitions intact.
     * 
     * @return void
     * 
     */
    public function __clone()
    {
        $this->services = array();
        $this->forge = clone $this->forge;
    }
    /**
     * 
     * Lock the Container so that configuration cannot be accessed externally,
     * and no new service definitions can be added.
     * 
     * @return void
     * 
     */
    public function lock()
    {
        $this->locked = true;
    }
    /**
     * 
     * Is the Container locked?
     * 
     * @return bool
     * 
     */
    public function isLocked()
    {
        return $this->locked;
    }
    /**
     * 
     * Gets the Forge object used for creating new instances.
     * 
     * @return array
     * 
     */
    public function getForge()
    {
        return $this->forge;
    }
    /**
     * 
     * Does a particular service definition exist?
     * 
     * @param string $key The service key to look up.
     * 
     * @return bool
     * 
     */
    public function has($key)
    {
        return isset($this->defs[$key]);
    }
    /**
     * 
     * Sets a service definition by name. If you set a service as a Closure,
     * it is automatically treated as a Lazy. (Note that is has to be a
     * Closure, not just any callable, to be treated as a Lazy; this is
     * because the actual service object itself might be callable via an
     * __invoke() method.)
     * 
     * @param string $key The service key.
     * 
     * @param object $val The service object; if a Closure, is treated as a
     * Lazy.
     * 
     * @throws Exception\ContainerLocked when the Container is locked.
     * 
     * @throws Exception\Service
     * 
     * @return $this
     * 
     */
    public function set($key, $val)
    {
        if ($this->isLocked()) {
            throw new Exception\ContainerLocked();
        }
        if (!is_object($val)) {
            throw new Exception\ServiceInvalid($key);
        }
        if ($val instanceof \Closure) {
            $val = new Lazy($val);
        }
        $this->defs[$key] = $val;
        return $this;
    }
    /**
     * 
     * Gets a service object by key, lazy-loading it as needed.
     * 
     * @param string $key The service to get.
     * 
     * @return object
     * 
     * @throws Exception\ServiceNotFound when the requested service
     * does not exist.
     * 
     */
    public function get($key)
    {
        // does the definition exist?
        if (!$this->has($key)) {
            throw new Exception\ServiceNotFound($key);
        }
        // has it been instantiated?
        if (!isset($this->services[$key])) {
            // instantiate it from its definition.
            $service = $this->defs[$key];
            // lazy-load as needed
            if ($service instanceof Lazy) {
                $service = $service();
            }
            // retain
            $this->services[$key] = $service;
        }
        // done
        return $this->services[$key];
    }
    /**
     * 
     * Gets the list of instantiated services.
     * 
     * @return array
     * 
     */
    public function getServices()
    {
        return array_keys($this->services);
    }
    /**
     * 
     * Gets the list of service definitions.
     * 
     * @return array
     * 
     */
    public function getDefs()
    {
        return array_keys($this->defs);
    }
    /**
     * 
     * Returns a Lazy containing a general-purpose callable. Use this when you
     * have complex logic or heavy overhead when creating a param that may or 
     * may not need to be loaded.
     * 
     *      $di->params['ClassName']['param_name'] = Lazy(function () {
     *          return include 'filename.php';
     *      });
     * 
     * @param callable $callable The callable functionality.
     * 
     * @return Lazy A lazy-load object that contains the calllable.
     * 
     */
    public function lazy(callable $callable)
    {
        return new Lazy($callable);
    }
    /**
     * 
     * Returns a Lazy that gets a service. This allows you to replace the
     * following idiom ...
     * 
     *      $di->params['ClassName']['param_name'] = new \Aura\Di\Lazy(function() use ($di)) {
     *          return $di->get('service');
     *      }
     * 
     * ... with the following:
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazyGet('service');
     * 
     * @param string $key The service name; it does not need to exist yet.
     * 
     * @return Lazy A lazy-load object that gets the named service.
     * 
     */
    public function lazyGet($key)
    {
        $self = $this;
        return $this->lazy(function () use($self, $key) {
            return $self->get($key);
        });
    }
    /**
     * 
     * Returns a new instance of the specified class, optionally
     * with additional override parameters.
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setters Override setters for the instance.
     * 
     * @return object An instance of the requested class.
     * 
     */
    public function newInstance($class, array $params = array(), array $setters = array())
    {
        return $this->forge->newInstance($class, $params, $setters);
    }
    /**
     * 
     * Returns a Lazy that creates a new instance. This allows you to replace
     * the following idiom:
     * 
     *      $di->params['ClassName']['param_name'] = new \Aura\Di\Lazy(function () use ($di)) {
     *          return $di->newInstance('OtherClass', [...]);
     *      });
     * 
     * ... with the following:
     * 
     *      $di->params['ClassName']['param_name'] = $di->lazyNew('OtherClass', [...]);
     * 
     * @param string $class The type of class of instantiate.
     * 
     * @param array $params Override parameters for the instance.
     * 
     * @param array $setters Override setters for the instance
     * 
     * @return Lazy A lazy-load object that creates the new instance.
     * 
     */
    public function lazyNew($class, array $params = array(), array $setters = array())
    {
        $forge = $this->getForge();
        return $this->lazy(function () use($forge, $class, $params, $setters) {
            return $forge->newInstance($class, $params, $setters);
        });
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\Container as AuraContainer;
use Aura\Di\ContainerInterface;
/**
 * Dependency injection container.
 *
 * @package Ray.Di
 */
class Container extends AuraContainer implements ContainerInterface
{
    
}
/**
 *
 * This file is part of the Aura Project for PHP.
 *
 * @package Aura.Di
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Di;

/**
 *
 * Defines the interface for Forge dependencies.
 *
 * @package Aura.Di
 *
 */
interface ForgeInterface
{
    /**
     *
     * Gets the injected Config object.
     *
     * @return ConfigInterface
     *
     */
    public function getConfig();
    /**
     *
     * Creates and returns a new instance of a class using
     * the configuration parameters, optionally with overriding params and setters.
     *
     * @param string $class The class to instantiate.
     *
     * @param array $params An associative array of override parameters where
     * the key is the name of the constructor parameter and the value is the
     * parameter value to use.
     *
     * @param array $setters An associative array of override setters where
     * the key is the name of the setter method to call and the value is the
     * value to be passed to the setter method.
     *
     * @return object
     *
     */
    public function newInstance($class, array $params = array(), array $setters = array());
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Di
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Di;

/**
 * 
 * Creates objects using reflection and the specified configuration values.
 * 
 * @package Aura.Di
 * 
 */
class Forge implements ForgeInterface
{
    /**
     * 
     * A Config object to get parameters for object instantiation and
     * \ReflectionClass instances.
     * 
     * @var Config
     * 
     */
    protected $config;
    /**
     * 
     * Constructor.
     * 
     * @param ConfigInterface $config A configuration object.
     * 
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }
    /**
     * 
     * When cloning this Forge, create a separate Config object for the clone.
     * 
     * @return void
     * 
     */
    public function __clone()
    {
        $this->config = clone $this->config;
    }
    /**
     * 
     * Gets the injected Config object.
     * 
     * @return ConfigInterface
     * 
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
     * 
     * Creates and returns a new instance of a class using reflection and
     * the configuration parameters, optionally with overriding params.
     * 
     * Parameters that are Lazy are invoked before instantiation.
     * 
     * @param string $class The class to instantiate.
     * 
     * @param array $params An associative array of override parameters where
     * the key is the name of the constructor parameter and the value is the
     * parameter value to use.
     * 
     * @param array $setters An associative array of override setters where
     * the key is the name of the setter method to call and the value is the
     * value to be passed to the setter method.
     * 
     * @return object
     * 
     */
    public function newInstance($class, array $params = array(), array $setters = array())
    {
        list($config, $setter) = $this->config->fetch($class);
        $params = array_merge($config, (array) $params);
        // lazy-load params as needed
        foreach ($params as $key => $val) {
            if ($params[$key] instanceof Lazy) {
                $params[$key] = $params[$key]();
            }
        }
        // merge the setters
        $setters = array_merge($setter, $setters);
        // create the new instance
        $call = array($this->config->getReflect($class), 'newInstance');
        $object = call_user_func_array($call, $params);
        // call setters after creation
        foreach ($setters as $method => $value) {
            // does the specified setter method exist?
            if (method_exists($object, $method)) {
                // lazy-load values as needed
                if ($value instanceof Lazy) {
                    $value = $value();
                }
                // call the setter
                $object->{$method}($value);
            }
        }
        // done!
        return $object;
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Aura\Di\Forge as AuraForge;
use Aura\Di\ForgeInterface;
/**
 *
 * Creates objects using reflection and the specified configuration values.
 *
 * @package Ray.Di
 */
class Forge extends AuraForge implements ForgeInterface
{
    
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Di
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Di;

/**
 * 
 * Retains and unifies class constructor parameter values with external values.
 * 
 * @package Aura.Di
 * 
 */
interface ConfigInterface
{
    /**
     * 
     * Fetches the unified constructor values and external values.
     * 
     * @param string $class The class name to fetch values for.
     * 
     * @return array An associative array of constructor values for the class.
     * 
     */
    public function fetch($class);
    /**
     * 
     * Gets the $params property.
     * 
     * @return \ArrayObject
     * 
     */
    public function getParams();
    /**
     * 
     * Gets the $setter property.
     * 
     * @return \ArrayObject
     * 
     */
    public function getSetter();
    /**
     * 
     * Gets a retained ReflectionClass; if not already retained, creates and
     * retains one before returning it.
     * 
     * @param string $class The class to reflect on.
     * 
     * @return \ReflectionClass
     * 
     */
    public function getReflect($class);
}
/**
 * This file is taken from Aura.Di(https://github.com/auraphp/Aura.Di) and modified.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * @see     https://github.com/auraphp/Aura.Di
 *
 */
namespace Ray\Di;

use Aura\Di\ConfigInterface;
use ArrayObject;
use ReflectionClass;
use ReflectionMethod;
/**
 * Retains and unifies class configurations.
 *
 * @package Ray.Di
 */
class Config implements ConfigInterface
{
    /**
     * Parameter index number
     */
    const INDEX_PARAM = 0;
    /**
     * Setter index number
     */
    const INDEX_SETTER = 1;
    /**
     * Definition index number
     */
    const INDEX_DEFINITION = 2;
    /**
     *
     * Constructor params from external configuration in the form
     * `$params[$class][$name] = $value`.
     *
     * @var \ArrayObject
     *
     */
    protected $params;
    /**
     *
     * An array of retained ReflectionClass instances; this is as much for
     * the Forge as it is for Config.
     *
     * @var array
     *
     */
    protected $reflect = array();
    /**
     *
     * Setter definitions in the form of `$setter[$class][$method] = $value`.
     *
     * @var \ArrayObject
     *
     */
    protected $setter;
    /**
     *
     * Constructor params and setter definitions, unified across class
     * defaults, inheritance hierarchies, and external configurations.
     *
     * @var array
     *
     */
    protected $unified = array();
    /**
     * Method parameters
     *
     * $params[$class][$method] = [$param1varName, $param2varName ...]
     *
     * @var array
     */
    protected $methodReflect;
    /**
     * Class annotated definition. object life cycle, dependency injection.
     *
     * `$definition[$class]['Scope'] = $value`
     * `$definition[$class]['PostConstruct'] = $value`
     * `$definition[$class]['PreDestroy'] = $value`
     * `$definition[$class]['Inject'] = $value`
     *
     * @var Definition
     */
    protected $definition;
    /**
     * Annotation scanner
     *
     * @var AnnotationInterface
     */
    protected $annotation;
    /**
     * Constructor
     *
     * @param AnnotationInterface $annotation
     */
    public function __construct(AnnotationInterface $annotation)
    {
        $this->reset();
        $this->annotation = $annotation;
    }
    /**
     *
     * When cloning this object, reset the params and setter values (but
     * leave the reflection values in place).
     *
     * @return void
     *
     */
    public function __clone()
    {
        $this->reset();
    }
    /**
     *
     * Resets the params and setter values.
     *
     * @return void
     *
     */
    protected function reset()
    {
        $this->params = new ArrayObject();
        $this->params['*'] = array();
        $this->setter = new ArrayObject();
        $this->setter['*'] = array();
        $this->definition = new Definition(array());
        $this->definition['*'] = array();
        $this->methodReflect = new ArrayObject();
    }
    /**
     *
     * Gets the $params property.
     *
     * @return \ArrayObject
     *
     */
    public function getParams()
    {
        return $this->params;
    }
    /**
     *
     * Gets the $setter property.
     *
     * @return \ArrayObject
     *
     */
    public function getSetter()
    {
        return $this->setter;
    }
    /**
     *
     * Gets the $definition property.
     *
     * @return Definition
     *
     */
    public function getDefinition()
    {
        return $this->definition;
    }
    /**
     *
     * Returns a \ReflectionClass for a named class.
     *
     * @param string $class The class to reflect on.
     *
     * @return \ReflectionClass
     *
     */
    public function getReflect($class)
    {
        if (!isset($this->reflect[$class])) {
            $this->reflect[$class] = new ReflectionClass($class);
        }
        return $this->reflect[$class];
    }
    /**
     *
     * Fetches the unified constructor params and setter values for a class.
     *
     * @param string $class The class name to fetch values for.
     *
     * @return array An array with two elements; 0 is the constructor values
     * for the class, and 1 is the setter methods and values for the class.
     * 2 is the class definition.
     */
    public function fetch($class)
    {
        // have values already been unified for this class?
        if (isset($this->unified[$class])) {
            return $this->unified[$class];
        }
        // fetch the values for parents so we can inherit them
        $parentClass = get_parent_class($class);
        if ($parentClass) {
            // parent class values
            list($parent_params, $parent_setter, $parent_definition) = $this->fetch($parentClass);
        } else {
            // no more parents; get top-level values for all classes
            $parent_params = $this->params['*'];
            $parent_setter = $this->setter['*'];
            // class annotated definition
            $parent_definition = $this->annotation->getDefinition($class);
        }
        // stores the unified config and setter values
        $unified_params = array();
        // reflect on the class
        $classReflection = $this->getReflect($class);
        // does it have a constructor?
        $constructorReflection = $classReflection->getConstructor();
        if ($constructorReflection) {
            // reflect on what params to pass, in which order
            $params = $constructorReflection->getParameters();
            foreach ($params as $param) {
                $name = $param->name;
                $explicit = $this->params->offsetExists($class) && isset($this->params[$class][$name]);
                if ($explicit) {
                    // use the explicit value for this class
                    $unified_params[$name] = $this->params[$class][$name];
                } elseif (isset($parent_params[$name])) {
                    // use the implicit value for the parent class
                    $unified_params[$name] = $parent_params[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    // use the external value from the constructor
                    $unified_params[$name] = $param->getDefaultValue();
                } else {
                    // no value, use a null placeholder
                    $unified_params[$name] = null;
                }
            }
        }
        // merge the setters
        if (isset($this->setter[$class])) {
            $unified_setter = array_merge($parent_setter, $this->setter[$class]);
        } else {
            $unified_setter = $parent_setter;
        }
        // merge the definitions
        $definition = isset($this->definition[$class]) ? $this->definition[$class] : $this->annotation->getDefinition($class);
        $unified_definition = new Definition(array_merge($parent_definition->getArrayCopy(), $definition->getArrayCopy()));
        $this->definition[$class] = $unified_definition;
        // done, return the unified values
        $this->unified[$class][0] = $unified_params;
        $this->unified[$class][1] = $unified_setter;
        $this->unified[$class][2] = $unified_definition;
        return $this->unified[$class];
    }
    /**
     *
     * Returns a \ReflectionClass for a named class.
     *
     * @param string $class  The class to reflect on
     * @param string $method The method to reflect on
     *
     * @return \ReflectionMethod
     *
     */
    public function getMethodReflect($class, $method)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        if (!isset($this->reflect[$class]) || !is_array($this->reflect[$class])) {
            $methodRef = new ReflectionMethod($class, $method);
            $this->methodReflect[$class][$method] = $methodRef;
        }
        return $this->methodReflect[$class][$method];
    }
    /**
     * Remove reflection property
     *
     * @return array
     */
    public function __sleep()
    {
        return array('params', 'setter', 'unified', 'definition', 'annotation');
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Annotation scanner.
 *
 * @package Ray.Di
 */
interface AnnotationInterface
{
    /**
     * Get class definition by annotation
     *
     * @param string $class
     *
     * @return Definition
     */
    public function getDefinition($class);
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionMethod;
/**
 * Annotation scanner
 *
 * @package Ray.Di
 */
class Annotation implements AnnotationInterface
{
    /**
     * User defined annotation
     *
     * $definition[Annotation::USER][$methodName] = [$annotation1, $annotation2 .. ]
     *
     * @var array
     */
    const USER = 'user';
    /**
     * Class definition (new)
     *
     * @var Definition
     */
    protected $newDefinition;
    /**
     * Class definition
     *
     * @var Definition
     */
    protected $definition;
    /**
     * Class definitions for in-memory cache
     *
     * @var Definition[]
     */
    protected $definitions = array();
    /**
     * Annotation reader
     *
     * @var \Doctrine\Common\Annotations\Reader;
     */
    protected $reader;
    /**
     * Constructor
     *
     * @param Definition $definition
     * @param Reader     $reader
     */
    public function __construct(Definition $definition, Reader $reader)
    {
        $this->newDefinition = $definition;
        $this->reader = $reader;
    }
    /**
     * Return class definition by annotation
     *
     * @param string $className
     *
     * @return array
     * @throws Exception\NotReadable
     */
    public function getDefinition($className)
    {
        if (isset($this->definitions[$className])) {
            return $this->definitions[$className];
        }
        $this->definition = clone $this->newDefinition;
        try {
            $class = new ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new Exception\NotReadable($className, 0, $e);
        }
        $annotations = $this->reader->getClassAnnotations($class);
        $classDefinition = $this->getDefinitionFormat($annotations);
        foreach ($classDefinition as $key => $value) {
            $this->definition[$key] = $value;
        }
        // Method Annotation
        $this->setMethodDefinition($class);
        $this->definitions[$className] = $this->definition;
        return $this->definition;
    }
    /**
     * Return definition format from annotations
     *
     * @param array $annotations
     * @param bool  $returnValue
     *
     * @return array [$annotation => $value][]
     */
    private function getDefinitionFormat(array $annotations, $returnValue = true)
    {
        $result = array();
        foreach ($annotations as $annotation) {
            $annotationName = $this->getAnnotationName($annotation);
            $value = $annotation;
            if ($returnValue === true) {
                $value = isset($annotation->value) ? $annotation->value : null;
            }
            $result[$annotationName] = $value;
        }
        return $result;
    }
    /**
     * Set method definition
     *
     * @param ReflectionClass $class
     *
     * @return void
     */
    private function setMethodDefinition(ReflectionClass $class)
    {
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $annotations = $this->reader->getMethodAnnotations($method);
            $methodAnnotation = $this->getDefinitionFormat($annotations, false);
            foreach ($methodAnnotation as $key => $value) {
                $this->setAnnotationName($key, $method, $methodAnnotation);
            }
            // user land annotation by method
            foreach ($annotations as $annotation) {
                $annotationName = $this->getAnnotationName($annotation);
                $this->definition->setUserAnnotationByMethod($annotationName, $method->name, $annotation);
            }
        }
    }
    /**
     * Return annotation name from annotation class name
     *
     * @param $annotation
     *
     * @return mixed
     */
    private function getAnnotationName($annotation)
    {
        $classPath = explode('\\', get_class($annotation));
        $annotationName = array_pop($classPath);
        return $annotationName;
    }
    /**
     * Set annotation key-value for DI
     *
     * @param string           $name        annotation name
     * @param ReflectionMethod $method
     * @param array            $annotations
     *
     * @return void
     * @throws Exception\MultipleAnnotationNotAllowed
     */
    private function setAnnotationName($name, ReflectionMethod $method, array $annotations)
    {
        if ($name === Definition::POST_CONSTRUCT || $name == Definition::PRE_DESTROY) {
            if (isset($this->definition[$name]) && $this->definition[$name]) {
                $msg = "@{$name} in " . $method->getDeclaringClass()->name;
                throw new Exception\MultipleAnnotationNotAllowed($msg);
            } else {
                $this->definition[$name] = $method->name;
            }
            return;
        }
        if ($name === Definition::INJECT) {
            $this->setSetterInjectDefinition($annotations, $method);
            return;
        }
        if ($name === Definition::NAMED) {
            return;
        }
        // user land annotation by name
        $this->definition->setUserAnnotationMethodName($name, $method->name);
    }
    /**
     * Set setter inject definition
     *
     * @param array            $methodAnnotation
     * @param ReflectionMethod $method
     *
     * @return void
     */
    private function setSetterInjectDefinition($methodAnnotation, ReflectionMethod $method)
    {
        $nameParameter = false;
        if (isset($methodAnnotation[Definition::NAMED])) {
            $named = $methodAnnotation[Definition::NAMED];
            $nameParameter = $named->value;
        }
        $named = $nameParameter !== false ? $this->getNamed($nameParameter) : array();
        $parameters = $method->getParameters();
        $paramsInfo = array();
        foreach ($parameters as $parameter) {
            /** @var $parameter \ReflectionParameter */
            $class = $parameter->getClass();
            $typehint = $class ? $class->getName() : '';
            $typehintBy = $typehint ? $this->getTypeHintDefaultInjection($typehint) : array();
            $pos = $parameter->getPosition();
            if (is_string($named)) {
                $name = $named;
            } elseif (isset($named[$parameter->name])) {
                $name = $named[$parameter->name];
            } else {
                $name = Definition::NAME_UNSPECIFIED;
            }
            $optionalInject = $methodAnnotation[Definition::INJECT]->optional;
            $paramsInfo[] = array(Definition::PARAM_POS => $pos, Definition::PARAM_TYPEHINT => $typehint, Definition::PARAM_NAME => $parameter->name, Definition::PARAM_ANNOTATE => $name, Definition::PARAM_TYPEHINT_BY => $typehintBy, Definition::OPTIONAL => $optionalInject);
        }
        $paramInfo[$method->name] = $paramsInfo;
        $this->definition[Definition::INJECT][Definition::INJECT_SETTER][] = $paramInfo;
    }
    /**
     * Get default injection by typehint
     *
     * this works as default bindings.
     *
     * @param string $typehint
     *
     * @return array
     */
    private function getTypeHintDefaultInjection($typehint)
    {
        static $definition = array();
        if (isset($definition[$typehint])) {
            $hintDef = $definition[$typehint];
        } else {
            //$annotations = $this->docParser->parse($doc, 'class ' . $typehint);
            $annotations = $this->reader->getClassAnnotations(new ReflectionClass($typehint));
            $hintDef = $this->getDefinitionFormat($annotations);
            $definition[$typehint] = $hintDef;
        }
        // @ImplementBy as default
        if (isset($hintDef[Definition::IMPLEMENTEDBY])) {
            $result = array(Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $hintDef[Definition::IMPLEMENTEDBY]);
            return $result;
        }
        // @ProvidedBy as default
        if (isset($hintDef[Definition::PROVIDEDBY])) {
            $result = array(Definition::PARAM_TYPEHINT_METHOD_PROVIDEDBY, $hintDef[Definition::PROVIDEDBY]);
            return $result;
        }
        // this typehint is class, not a interface.
        if (class_exists($typehint)) {
            $class = new ReflectionClass($typehint);
            if ($class->isAbstract() === false) {
                $result = array(Definition::PARAM_TYPEHINT_METHOD_IMPLEMETEDBY, $typehint);
                return $result;
            }
        }
        return array();
    }
    /**
     * Get Named
     *
     * @param string $nameParameter "value" or "key1=value1,ke2=value2"
     *
     * @return array [$paramName => $named][]
     * @throws Exception\Named
     */
    private function getNamed($nameParameter)
    {
        // single annotation @Named($annotation)
        if (preg_match('/^[a-zA-Z0-9_]+$/', $nameParameter)) {
            return $nameParameter;
        }
        // multi annotation @Named($varName1=$annotate1,$varName2=$annotate2)
        // http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
        preg_match_all('/([^=,]*)=("[^"]*"|[^,"]*)/', $nameParameter, $matches);
        if ($matches[0] === array()) {
            throw new Exception\Named();
        }
        $result = array();
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $result[$matches[1][$i]] = $matches[2][$i];
        }
        return $result;
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use ArrayObject;
/**
 * Retains target class inject definition.
 *
 * @package Ray.Di
 */
class Definition extends ArrayObject
{
    /**
     * Post construct annotation
     *
     * @var string
     */
    const POST_CONSTRUCT = 'PostConstruct';
    /**
     * PreDestroy annotation
     *
     * @var string
     */
    const PRE_DESTROY = 'PreDestroy';
    /**
     * Inject annotation
     *
     * @var string
     */
    const INJECT = 'Inject';
    /**
     * Provide annotation
     *
     * @var string
     */
    const PROVIDE = 'Provide';
    /**
     * Scope annotation
     *
     * @var string
     */
    const SCOPE = 'Scope';
    /**
     * ImplementedBy annotation (Just-in-time Binding)
     *
     * @var string
     */
    const IMPLEMENTEDBY = 'ImplementedBy';
    /**
     * ProvidedBy annotation (Just-in-time Binding)
     *
     * @var string
     */
    const PROVIDEDBY = 'ProvidedBy';
    /**
     * Named annotation
     *
     * @var string
     */
    const NAMED = 'Named';
    /**
     * PreDestroy annotation
     *
     * @var string
     */
    const NAME_UNSPECIFIED = '*';
    /**
     * Setter inject definition
     *
     * @var string
     */
    const INJECT_SETTER = 'setter';
    /**
     * Parameter position
     *
     * @var string
     */
    const PARAM_POS = 'pos';
    /**
     * Typehint
     *
     * @var string
     */
    const PARAM_TYPEHINT = 'typehint';
    /**
     * Param typehint default concrete class / provider class
     *
     * @var array [$typehintMethod, $className>]
     */
    const PARAM_TYPEHINT_BY = 'typehint_by';
    /**
     * Param typehint default concrete class
     *
     * @var string
     */
    const PARAM_TYPEHINT_METHOD_IMPLEMETEDBY = 'implementedby';
    /**
     * Param typehint default provider
     *
     * @var string
     */
    const PARAM_TYPEHINT_METHOD_PROVIDEDBY = 'providedby';
    /**
     * Param var name
     *
     * @var string
     */
    const PARAM_NAME = 'name';
    /**
     * Param named annotation
     *
     * @var string
     */
    const PARAM_ANNOTATE = 'annotate';
    /**
     * Aspect annotation
     *
     * @var string
     */
    const ASPECT = 'Aspect';
    /**
     * User defined interceptor annotation
     *
     * @var string
     */
    const USER = 'user';
    /**
     * OPTIONS
     *
     * @var string
     */
    const OPTIONS = 'options';
    /**
     * BINDING
     *
     * @var string
     */
    const BINDING = 'binding';
    /**
     * BY_METHOD
     *
     * @var string
     */
    const BY_METHOD = 'by_method';
    /**
     * BY_NAME
     *
     * @var string
     */
    const BY_NAME = 'by_name';
    /**
     * Optional Inject
     *
     * @var string
     */
    const OPTIONAL = 'optional';
    /**
     * Definition default
     *
     * @var array
     */
    private $defaults = array(self::SCOPE => Scope::PROTOTYPE, self::POST_CONSTRUCT => null, self::PRE_DESTROY => null, self::INJECT => array(), self::IMPLEMENTEDBY => array(), self::USER => array(), self::OPTIONAL => array());
    /**
     * Constructor
     *
     * @param array $defaults default definition set
     */
    public function __construct(array $defaults = null)
    {
        $defaults = $defaults ?: $this->defaults;
        parent::__construct($defaults);
    }
    /**
     * Return is-defined
     *
     * @return bool
     */
    public function hasDefinition()
    {
        $hasDefinition = $this->getArrayCopy() !== $this->defaults;
        return $hasDefinition;
    }
    /**
     * Set user annotation by name
     *
     * @param string $annotationName
     * @param string $methodName
     *
     * @return void
     */
    public function setUserAnnotationMethodName($annotationName, $methodName)
    {
        $this[self::BY_NAME][$annotationName][] = $methodName;
    }
    /**
     * Return user annotation by annotation name
     *
     * @param $annotationName
     *
     * @return array [$methodName, $methodAnnotation]
     */
    public function getUserAnnotationMethodName($annotationName)
    {
        $hasUserAnnotation = isset($this[self::BY_NAME]) && isset($this[self::BY_NAME][$annotationName]);
        $result = $hasUserAnnotation ? $this[Definition::BY_NAME][$annotationName] : null;
        return $result;
    }
    /**
     * setUserAnnotationByMethod
     *
     * @param string $annotationName
     * @param string $methodName
     * @param object $methodAnnotation
     *
     * @return void
     */
    public function setUserAnnotationByMethod($annotationName, $methodName, $methodAnnotation)
    {
        $this[self::BY_METHOD][$methodName][$annotationName][] = $methodAnnotation;
    }
    /**
     * Return user annotation by method name
     *
     * @param string $methodName
     *
     * @return array [$annotationName, $methodAnnotation][]
     */
    public function getUserAnnotationByMethod($methodName)
    {
        $result = isset($this[self::BY_METHOD]) && isset($this[self::BY_METHOD][$methodName]) ? $this[self::BY_METHOD][$methodName] : null;
        return $result;
    }
    /**
     * Return class annotation definition information.
     *
     * @return string
     */
    public function __toString()
    {
        return var_export($this, true);
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Scope Definition
 *
 * @package Ray.Di
 */
class Scope
{
    /**
     * Singleton scope
     *
     * @var string
     */
    const SINGLETON = 'singleton';
    /**
     * Prototype scope
     *
     * @var string
     */
    const PROTOTYPE = 'prototype';
}
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */
namespace Doctrine\Common;

/**
 * Base class for writing simple lexers, i.e. for creating small DSLs.
 *
 * @since   2.0
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @todo Rename: AbstractLexer
 */
abstract class Lexer
{
    /**
     * @var array Array of scanned tokens
     */
    private $tokens = array();
    /**
     * @var integer Current lexer position in input string
     */
    private $position = 0;
    /**
     * @var integer Current peek of current lexer position
     */
    private $peek = 0;
    /**
     * @var array The next token in the input.
     */
    public $lookahead;
    /**
     * @var array The last matched/seen token.
     */
    public $token;
    /**
     * Sets the input data to be tokenized.
     *
     * The Lexer is immediately reset and the new input tokenized.
     * Any unprocessed tokens from any previous input are lost.
     *
     * @param string $input The input to be tokenized.
     */
    public function setInput($input)
    {
        $this->tokens = array();
        $this->reset();
        $this->scan($input);
    }
    /**
     * Resets the lexer.
     */
    public function reset()
    {
        $this->lookahead = null;
        $this->token = null;
        $this->peek = 0;
        $this->position = 0;
    }
    /**
     * Resets the peek pointer to 0.
     */
    public function resetPeek()
    {
        $this->peek = 0;
    }
    /**
     * Resets the lexer position on the input to the given position.
     *
     * @param integer $position Position to place the lexical scanner
     */
    public function resetPosition($position = 0)
    {
        $this->position = $position;
    }
    /**
     * Checks whether a given token matches the current lookahead.
     *
     * @param integer|string $token
     * @return boolean
     */
    public function isNextToken($token)
    {
        return null !== $this->lookahead && $this->lookahead['type'] === $token;
    }
    /**
     * Checks whether any of the given tokens matches the current lookahead
     *
     * @param array $tokens
     * @return boolean
     */
    public function isNextTokenAny(array $tokens)
    {
        return null !== $this->lookahead && in_array($this->lookahead['type'], $tokens, true);
    }
    /**
     * Moves to the next token in the input string.
     *
     * A token is an associative array containing three items:
     *  - 'value'    : the string value of the token in the input string
     *  - 'type'     : the type of the token (identifier, numeric, string, input
     *                 parameter, none)
     *  - 'position' : the position of the token in the input string
     *
     * @return array|null the next token; null if there is no more tokens left
     */
    public function moveNext()
    {
        $this->peek = 0;
        $this->token = $this->lookahead;
        $this->lookahead = isset($this->tokens[$this->position]) ? $this->tokens[$this->position++] : null;
        return $this->lookahead !== null;
    }
    /**
     * Tells the lexer to skip input tokens until it sees a token with the given value.
     *
     * @param string $type The token type to skip until.
     */
    public function skipUntil($type)
    {
        while ($this->lookahead !== null && $this->lookahead['type'] !== $type) {
            $this->moveNext();
        }
    }
    /**
     * Checks if given value is identical to the given token
     *
     * @param mixed $value
     * @param integer $token
     * @return boolean
     */
    public function isA($value, $token)
    {
        return $this->getType($value) === $token;
    }
    /**
     * Moves the lookahead token forward.
     *
     * @return array | null The next token or NULL if there are no more tokens ahead.
     */
    public function peek()
    {
        if (isset($this->tokens[$this->position + $this->peek])) {
            return $this->tokens[$this->position + $this->peek++];
        } else {
            return null;
        }
    }
    /**
     * Peeks at the next token, returns it and immediately resets the peek.
     *
     * @return array|null The next token or NULL if there are no more tokens ahead.
     */
    public function glimpse()
    {
        $peek = $this->peek();
        $this->peek = 0;
        return $peek;
    }
    /**
     * Scans the input string for tokens.
     *
     * @param string $input a query string
     */
    protected function scan($input)
    {
        static $regex;
        if (!isset($regex)) {
            $regex = '/(' . implode(')|(', $this->getCatchablePatterns()) . ')|' . implode('|', $this->getNonCatchablePatterns()) . '/i';
        }
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($regex, $input, -1, $flags);
        foreach ($matches as $match) {
            // Must remain before 'value' assignment since it can change content
            $type = $this->getType($match[0]);
            $this->tokens[] = array('value' => $match[0], 'type' => $type, 'position' => $match[1]);
        }
    }
    /**
     * Gets the literal for a given token.
     *
     * @param integer $token
     * @return string
     */
    public function getLiteral($token)
    {
        $className = get_class($this);
        $reflClass = new \ReflectionClass($className);
        $constants = $reflClass->getConstants();
        foreach ($constants as $name => $value) {
            if ($value === $token) {
                return $className . '::' . $name;
            }
        }
        return $token;
    }
    /**
     * Lexical catchable patterns.
     *
     * @return array
     */
    protected abstract function getCatchablePatterns();
    /**
     * Lexical non-catchable patterns.
     *
     * @return array
     */
    protected abstract function getNonCatchablePatterns();
    /**
     * Retrieve token type. Also processes the token value if necessary.
     *
     * @param string $value
     * @return integer
     */
    protected abstract function getType(&$value);
}
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\Reader;
/**
 * Supports matching classes and methods.
 *
 * @package Ray.Di
 */
interface Matchable
{
    /**
     * Constructor
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader);
    /**
     * Any match
     *
     * @return Matcher
     */
    public function any();
    /**
     * Match binding annotation
     *
     * @param string $annotationName
     *
     * @return array
     */
    public function annotatedWith($annotationName);
    /**
     * Return subclass matched result
     *
     * @param string $superClass
     *
     * @return bool
     */
    public function subclassesOf($superClass);
    /**
     * Return match result
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return bool | array [$matcher, method]
     */
    public function __invoke($class, $target);
}
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\Reader;
use Ray\Aop\Exception\InvalidArgument as InvalidArgumentException;
use Ray\Aop\Exception\InvalidAnnotation;
use ReflectionClass;
/**
 * Matcher
 *
 * @package Ray.Aop
 */
/** @noinspection PhpDocMissingReturnTagInspection */
class Matcher implements Matchable
{
    /**
     * Match CLASS
     *
     * @var bool
     */
    const TARGET_CLASS = true;
    /**
     * Match Method
     *
     * @var bool
     */
    const TARGET_METHOD = false;
    /**
     * Annotation reader
     *
     * @var Reader
     */
    private $reader;
    /**
     * Lazy match method
     *
     * @var string
     */
    private $method;
    /**
     * Lazy match args
     *
     * @var array
     */
    private $args;
    /**
     * Constructor
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }
    /**
     * Return is annotate bindings
     *
     * @return boolean
     */
    public function isAnnotateBinding()
    {
        $isAnnotateBinding = $this->method === 'annotatedWith';
        return $isAnnotateBinding;
    }
    /**
     * Any match
     *
     * @return Matcher
     */
    public function any()
    {
        $this->method = __FUNCTION__;
        $this->args = null;
        return clone $this;
    }
    /**
     * Match binding annotation
     *
     * @param string $annotationName
     *
     * @return Matcher
     * @throws InvalidAnnotation
     */
    public function annotatedWith($annotationName)
    {
        if (!class_exists($annotationName)) {
            throw new InvalidAnnotation($annotationName);
        }
        $this->method = __FUNCTION__;
        $this->args = $annotationName;
        return clone $this;
    }
    /**
     * Return subclass matched result
     *
     * @param string $superClass
     *
     * @return Matcher
     */
    public function subclassesOf($superClass)
    {
        $this->method = __FUNCTION__;
        $this->args = $superClass;
        return clone $this;
    }
    /**
     * Return prefix match result
     *
     * @param string $prefix
     *
     * @return Matcher
     */
    public function startWith($prefix)
    {
        $this->method = __FUNCTION__;
        $this->args = $prefix;
        return clone $this;
    }
    /**
     * Return match(true)
     *
     * @param string $name   class or method name
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return Matcher
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isAny($name, $target)
    {
        if ($target === self::TARGET_CLASS) {
            return true;
        }
        if (substr($name, 0, 2) === '__') {
            return false;
        }
        if (in_array($name, array('offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset', 'append', 'getArrayCopy', 'count', 'getFlags', 'setFlags', 'asort', 'ksort', 'uasort', 'uksort', 'natsort', 'natcasesort', 'unserialize', 'serialize', 'getIterator', 'exchangeArray', 'setIteratorClass', 'getIterator', 'getIteratorClass'))) {
            return false;
        }
        return true;
    }
    /**
     * Return match result
     *
     * Return Match object if annotate bindings, which containing multiple results.
     * Otherwise return bool.
     *
     * @param string $class
     * @param bool   $target         self::TARGET_CLASS | self::TARGET_METHOD
     * @param string $annotationName
     *
     * @return bool | Matched[]
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isAnnotatedWith($class, $target, $annotationName)
    {
        $reader = $this->reader;
        if ($target === self::TARGET_CLASS) {
            $annotation = $reader->getClassAnnotation(new ReflectionClass($class), $annotationName);
            $hasAnnotation = $annotation ? true : false;
            return $hasAnnotation;
        }
        $methods = (new ReflectionClass($class))->getMethods();
        $result = array();
        foreach ($methods as $method) {
            new $annotationName();
            $annotation = $reader->getMethodAnnotation($method, $annotationName);
            if ($annotation) {
                $matched = new Matched();
                $matched->methodName = $method->name;
                $matched->annotation = $annotation;
                $result[] = $matched;
            }
        }
        return $result;
    }
    /**
     * Return subclass match.
     *
     * @param string $class
     * @param bool   $target     self::TARGET_CLASS | self::TARGET_METHOD
     * @param string $superClass
     *
     * @return bool
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isSubclassesOf($class, $target, $superClass)
    {
        if ($target === self::TARGET_METHOD) {
            throw new InvalidArgumentException($class);
        }
        try {
            $isSubClass = (new ReflectionClass($class))->isSubclassOf($superClass);
            if ($isSubClass === false) {
                $isSubClass = $class === $superClass;
            }
            return $isSubClass;
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * Return prefix match
     *
     * @param string $name
     * @param string $target
     * @param string $startWith
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isStartWith($name, $target, $startWith)
    {
        unset($target);
        $result = strpos($name, $startWith) === 0 ? true : false;
        return $result;
    }
    /**
     * Return match result
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return bool | array [$matcher, method]
     */
    public function __invoke($class, $target)
    {
        $args = array($class, $target);
        array_push($args, $this->args);
        $method = 'is' . $this->method;
        $matched = call_user_func_array(array($this, $method), $args);
        return $matched;
    }
    /**
     * __toString magic method
     *
     * @return string
     */
    public function __toString()
    {
        $result = $this->method . ':' . json_encode($this->args);
        return $result;
    }
}
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Bind method name to interceptors
 *
 * @package Ray.Aop
 */
interface BindInterface
{
    /**
     * Bind method to interceptors
     *
     * @param string $method
     * @param array  $interceptors
     * @param object $annotation   Binding annotation if annotate bind
     *
     * @return Bind
     */
    public function bindInterceptors($method, array $interceptors, $annotation = null);
    /**
     * Get matched Interceptor
     *
     * @param string $name class name
     *
     * @return mixed string|boolean matched method name
     */
    public function __invoke($name);
    /**
     * Make pointcuts to binding information
     *
     * @param string $class
     * @param array  $pointcuts
     *
     * @return Bind
     */
    public function bind($class, array $pointcuts);
}
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use ReflectionClass;
use ReflectionMethod;
use ArrayObject;
/**
 * Bind method name to interceptors
 *
 * @package Ray.Aop
 */
final class Bind extends ArrayObject implements BindInterface
{
    /**
     * Annotated binding annotation
     *
     * @var array [$method => $annotations]
     */
    public $annotation = array();
    /**
     * Bind method to interceptors
     *
     * @param string $method
     * @param array  $interceptors
     * @param object $annotation   Binding annotation if annotate bind
     *
     * @return Bind
     */
    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\BindInterface::bindInterceptors()
     */
    public function bindInterceptors($method, array $interceptors, $annotation = null)
    {
        if (!isset($this[$method])) {
            $this[$method] = $interceptors;
        } else {
            $this[$method] = array_merge($this[$method], $interceptors);
        }
        if ($annotation) {
            $this->annotation[$method] = $annotation;
        }
        return $this;
    }
    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\BindInterface::hasBinding()
     */
    public function hasBinding()
    {
        $hasImplicitBinding = count($this) ? true : false;
        return $hasImplicitBinding;
    }
    /**
     * (non-PHPDoc)
     * @see \Ray\Aop\BindInterface::bind()
     */
    public function bind($class, array $pointcuts)
    {
        foreach ($pointcuts as $pointcut) {
            /** @var $pointcut Pointcut */
            $classMatcher = $pointcut->classMatcher;
            $isClassMatch = $classMatcher($class, Matcher::TARGET_CLASS);
            if ($isClassMatch === true) {
                $method = $pointcut->methodMatcher->isAnnotateBinding() ? 'bindByAnnotateBinding' : 'bindByCallable';
                $this->{$method}($class, $pointcut->methodMatcher, $pointcut->interceptors);
            }
        }
        return $this;
    }
    /**
     * Bind interceptor by callable matcher
     *
     * @param string  $class
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     *
     * @return void
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function bindByCallable($class, Matcher $methodMatcher, array $interceptors)
    {
        $methods = (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $isMethodMatch = $methodMatcher($method->name, Matcher::TARGET_METHOD) === true;
            if ($isMethodMatch) {
                $this->bindInterceptors($method->name, $interceptors);
            }
        }
    }
    /**
     * Bind interceptor by annotation binding
     *
     * @param string  $class
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     *
     * @return void
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function bindByAnnotateBinding($class, Matcher $methodMatcher, array $interceptors)
    {
        $matches = (array) $methodMatcher($class, Matcher::TARGET_METHOD);
        if (!$matches) {
            return;
        }
        foreach ($matches as $matched) {
            if ($matched instanceof Matched) {
                $this->bindInterceptors($matched->methodName, $interceptors, $matched->annotation);
            }
        }
    }
    /**
     * Get matched Interceptor
     *
     * @param string $name class name
     *
     * @return mixed string|boolean matched method name
     */
    public function __invoke($name)
    {
        // pre compiled implicit matcher
        $interceptors = isset($this[$name]) ? $this[$name] : false;
        return $interceptors;
    }
    /**
     * to String
     *
     * for logging
     *
     * @return string
     */
    public function __toString()
    {
        $binds = array();
        foreach ($this as $method => $interceptors) {
            $inspectorsInfo = array();
            foreach ($interceptors as $interceptor) {
                $inspectorsInfo[] .= get_class($interceptor);
            }
            $inspectorsInfo = implode(',', $inspectorsInfo);
            $binds[] = "{$method} => " . $inspectorsInfo;
        }
        $result = implode(',', $binds);
        return $result;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Constant;

use Ray\Di\AbstractModule;
/**
 * Constants 'Named' module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class NamedModule extends AbstractModule
{
    /**
     * Constructor
     *
     * @param array $names
     */
    public function __construct(array $names)
    {
        $this->names = $names;
        parent::__construct();
    }
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        foreach ($this->names as $annotatedWith => $instance) {
            $this->bind()->annotatedWith($annotatedWith)->toInstance($instance);
        }
    }
}
/**
 * Module
 *
 * @package    Sandbox
 * @subpackage Module
 */
namespace BEAR\Package\Module;

use BEAR\Package;
use BEAR\Package\Module;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;
/**
 * Package module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class PackageModule extends AbstractModule
{
    private $scheme;
    /**
     * @param \Ray\Di\AbstractModule $module
     * @param \Ray\Aop\Matcher       $scheme
     */
    public function __construct(AbstractModule $module, $scheme)
    {
        parent::__construct($module);
        $this->scheme = $scheme;
    }
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->install(new SundayModule\Framework\FrameworkModule($this));
        $packageDir = dirname(dirname(dirname(dirname(dirname('/Users/kooriyama/git/BEAR.Package/src/BEAR/Package/Module')))));
        $this->bind()->annotatedWith('package_dir')->toInstance($packageDir);
        // Provide module (BEAR.Sunday extension interfaces)
        $this->install(new ProvideModule\ApplicationLogger\ApplicationLoggerModule());
        $this->install(new ProvideModule\TemplateEngine\Smarty\SmartyModule());
        $this->install(new ProvideModule\WebResponse\HttpFoundationModule());
        $this->install(new ProvideModule\ConsoleOutput\ConsoleOutputModule());
        $this->install(new ProvideModule\Router\MinRouterModule());
        $this->install(new ProvideModule\ResourceView\TemplateEngineRendererModule());
        $this->install(new ProvideModule\ResourceView\HalModule());
        // Package module
        $this->install(new Package\Module\Database\Dbal\DbalModule($this));
        $this->install(new Package\Module\Log\ZfLogModule());
        $this->install(new Package\Module\ExceptionHandle\HandleModule());
        // Sunday module
        $this->install(new SundayModule\SchemeModule($this->scheme));
        $this->install(new SundayModule\Resource\ApcModule());
        $this->install(new SundayModule\WebContext\AuraWebModule());
        $this->install(new SundayModule\Cqrs\CacheModule($this));
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Framework;

use BEAR\Sunday\Module;
use Ray\Di\Injector;
use Ray\Di\AbstractModule;
/**
 * Application module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class FrameworkModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        // core
        $this->install(new Module\Framework\ConstantModule());
        $this->install(new Module\Di\InjectorModule());
        $this->install(new Module\Resource\ResourceModule());
        $this->install(new Module\Code\CachedAnnotationModule());
        // extension
        $this->install(new Module\Cache\ApcModule());
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Framework;

use Ray\Di\AbstractModule;
/**
 * Output console module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ConstantModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('')->annotatedWith('is_prod')->toInstance(false);
        $sundayDir = dirname(dirname(dirname(dirname(dirname('/Users/kooriyama/git/BEAR.Package/vendor/bear/sunday/src/BEAR/Sunday/Module/Framework')))));
        $this->bind('')->annotatedWith('sunday_dir')->toInstance($sundayDir);
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Di;

use Ray\Di\Injector;
use Ray\Di\InjectorInterface;
use Ray\Di\AbstractModule;
/**
 * Application module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class InjectorModule extends AbstractModule
{
    private $injector;
    /**
     * Constructor
     *
     * @param InjectorInterface $injector
     */
    public function construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
        $logger = $this->requestInjection('BEAR\\Sunday\\Inject\\Logger\\Adapter');
        /** @var $logger \Ray\Di\LoggerInterface */
        $this->injector->setLogger($logger);
        parent::__construct();
    }
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $config = $this->dependencyInjector->getContainer()->getForge()->getConfig();
        $this->bind('Aura\\Di\\ConfigInterface')->toInstance($config);
        $this->bind('Ray\\Di\\InjectorInterface')->toInstance($this->dependencyInjector);
        $module = $this->dependencyInjector->getModule();
        $this->bind('Ray\\Di\\AbstractModule')->toInstance($module);
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Resource;

use Ray\Di\Injector;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;
/**
 * Resource module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ResourceModule extends AbstractModule
{
    private $injector;
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('Ray\\Di\\InjectorInterface')->toInstance($this->injector);
        $this->bind('BEAR\\Resource\\ResourceInterface')->to('BEAR\\Resource\\Resource')->in(Scope::SINGLETON);
        $this->bind('BEAR\\Resource\\InvokerInterface')->to('BEAR\\Resource\\Invoker')->in(Scope::SINGLETON);
        $this->bind('BEAR\\Resource\\LinkerInterface')->to('BEAR\\Resource\\Linker')->in(Scope::SINGLETON);
        $this->bind('BEAR\\Resource\\LoggerInterface')->annotatedWith('resource_logger')->to('BEAR\\Resource\\Logger');
        $this->bind('BEAR\\Resource\\LoggerInterface')->toProvider('BEAR\\Sunday\\Module\\Provider\\ResourceLoggerProvider');
        $this->bind('BEAR\\Resource\\HrefInterface')->to('BEAR\\Resource\\A');
        $this->bind('Aura\\Signal\\Manager')->toProvider('BEAR\\Sunday\\Module\\Provider\\SignalProvider')->in(Scope::SINGLETON);
        $this->bind('Guzzle\\Parser\\UriTemplate\\UriTemplateInterface')->to('Guzzle\\Parser\\UriTemplate\\UriTemplate');
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

/**
 * Interface for object provider. (lazy-loading)
 *
 * @package Ray.Di
 */
interface ProviderInterface
{
    /**
     * Get object
     *
     * @return object
     */
    public function get();
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Provider;

use Ray\Di\ProviderInterface;
use BEAR\Resource\LoggerInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * Resource logger
 *
 * @package BEAR.Sunday
 * @see     https://github.com/auraphp/Aura.Web.git
 */
class ResourceLoggerProvider implements ProviderInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * Set logger name
     *
     * @param LoggerInterface $logger
     *
     * @Inject
     * @Named("resource_logger")
     */
    public function setLoggerClassName(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Logger instance
     *
     * @var \BEAR\Resource\Logger
     */
    private static $instance;
    /**
     * Return instance
     *
     * @return AppInterface
     */
    public function get()
    {
        if (!self::$instance) {
            self::$instance = $this->logger;
        }
        return self::$instance;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Provider;

use Ray\Di\ProviderInterface as Provide;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
/**
 * Signal
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class SignalProvider implements Provide
{
    /**
     * Return instance
     *
     * @return Manager
     */
    public function get()
    {
        return new Manager(new HandlerFactory(), new ResultFactory(), new ResultCollection());
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Code;

use Ray\Di\AbstractModule;
/**
 * Cached annotation reader module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class CachedAnnotationModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('Doctrine\\Common\\Annotations\\Reader')->toProvider('BEAR\\Sunday\\Module\\Provider\\CachedReaderProvider');
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Provider;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcCache;
use Ray\Di\ProviderInterface as Provide;
/**
 * APC cached reader
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class CachedReaderProvider implements Provide
{
    /**
     * Return instance
     *
     * @return CachedReader
     */
    public function get()
    {
        $reader = new CachedReader(new AnnotationReader(), new ApcCache(), true);
        return $reader;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Cache;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;
/**
 * Cache module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ApcModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('Guzzle\\Cache\\AbstractCacheAdapter')->toProvider('BEAR\\Sunday\\Module\\Provider\\ApcCacheProvider')->in(Scope::SINGLETON);
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Inject;

/**
 * Inject tmp_dir
 *
 * @package BEAR.Sunday
 */
trait TmpDirInject
{
    /**
     * Tmp dir
     *
     * @var string
     */
    private $tmpDir;
    /**
     * Set tmp dir path
     *
     * @param string $tmpDir
     *
     * @Ray\Di\Di\Inject
     * @Ray\Di\Di\Named("tmp_dir")
     */
    public function setTmpDir($tmpDir)
    {
        $this->tmpDir = $tmpDir;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Provider;

use Doctrine\Common\Cache\ApcCache;
use Guzzle\Cache\DoctrineCacheAdapter as CacheAdapter;
use Ray\Di\ProviderInterface as Provide;
use BEAR\Sunday\Inject\TmpDirInject;
use Doctrine\Common\Cache\FilesystemCache;
/**
 * Cache
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ApcCacheProvider implements Provide
{
    use TmpDirInject;
    /**
     * Return instance
     *
     * @return CacheAdapter
     */
    public function get()
    {
        if (function_exists('apc_cache_info')) {
            $cache = new CacheAdapter(new ApcCache());
        } else {
            $cache = new CacheAdapter(new FilesystemCache($this->tmpDir));
        }
        return $cache;
    }
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * Annotation interface
 *
 * @package Ray.Di
 */
interface Annotation
{
    
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Di;

/**
 * Scope
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @package    Ray.Di
 * @subpackage Annotation
 */
final class Scope implements Annotation
{
    /**
     * Singleton
     *
     * @var string
     */
    const SINGLETON = 'singleton';
    /**
     * Prototype
     *
     * @var string
     */
    const PROTOTYPE = 'prototype';
    /**
     * Object lifecycle
     *
     * @var string
     */
    public $value = self::PROTOTYPE;
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;
/**
 * Application logger module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ApplicationLoggerModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        // log register
        $this->bind('BEAR\\Sunday\\Extension\\ApplicationLogger\\ApplicationLoggerInterface')->to(__NAMESPACE__ . '\\ApplicationLogger');
        // log writer
        $this->bind('BEAR\\Resource\\LogWriterInterface')->toProvider(__NAMESPACE__ . '\\ResourceLog\\WritersProvider')->in(Scope::SINGLETON);
        $this->bind('Ray\\Di\\LoggerInterface')->to('BEAR\\Package\\Provide\\Application\\DiLogger')->in(Scope::SINGLETON);
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Inject;

/**
 * Inject log dir
 *
 * @package    BEAR.Sunday
 * @subpackage Inject
 */
trait LogDirInject
{
    /**
     * Tmp dir
     *
     * @var string
     */
    private $logDir;
    /**
     * Set tmp dir path
     *
     * @param string $logDir
     *
     * @Ray\Di\Di\Inject
     * @Ray\Di\Di\Named("log_dir")
     */
    public function setLogDir($logDir)
    {
        $this->logDir = $logDir;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog;

use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Collection;
use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Zf2LogProvider;
use Zend\Log\Logger;
use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Zf2Log;
use Ray\Di\ProviderInterface;
use BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer\Fire;
/**
 * Writer provider
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class WritersProvider implements ProviderInterface
{
    use \BEAR\Sunday\Inject\LogDirInject;
    /**
     * @return Writer\Collection|object
     */
    public function get()
    {
        $writers = new Collection(array(new Fire(), new Zf2Log(new Zf2LogProvider($this->logDir))));
        return $writers;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;
/**
 * Smarty module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class SmartyModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\\Sunday\\Extension\\TemplateEngine\\TemplateEngineAdapterInterface')->to(__NAMESPACE__ . '\\SmartyAdapter')->in(Scope::SINGLETON);
        $this->bind('Smarty')->toProvider(__NAMESPACE__ . '\\SmartyProvider')->in(Scope::SINGLETON);
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Inject;

/**
 * Inject app dir
 *
 * @package    BEAR.Sunday
 * @subpackage Inject
 */
trait AppDirInject
{
    /**
     * App directory path
     *
     * @var string
     */
    private $appDir;
    /**
     * App directory path setter
     *
     * @param string $appDir
     *
     * @return void
     *
     * @Ray\Di\Di\Inject
     * @Ray\Di\Di\Named("app_dir")
     */
    public function setAppDir($appDir)
    {
        $this->appDir = $appDir;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

use BEAR\Sunday\Inject\TmpDirInject;
use BEAR\Sunday\Inject\AppDirInject;
use Ray\Di\ProviderInterface as Provide;
use Smarty;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
// @codingStandardsIgnoreFile
/**
 * Smarty3
 *
 * @see http://www.smarty.net/docs/ja/
 */
class SmartyProvider implements Provide
{
    use TmpDirInject;
    use AppDirInject;
    /**
     * Return instance
     *
     * @return Smarty
     */
    public function get()
    {
        $smarty = new Smarty();
        $appPlugin = $this->appDir . '/vendor/libs/smarty/plugin/';
        $frameworkPlugin = '/Users/kooriyama/git/BEAR.Package/src/BEAR/Package/Provide/TemplateEngine/Smarty' . '/plugin';
        $smarty->setCompileDir($this->tmpDir . '/smarty/template_c')->setCacheDir($this->tmpDir . '/smarty/cache')->setTemplateDir($this->appDir . '/Resource/View')->setPluginsDir(array_merge($smarty->getPluginsDir(), array($appPlugin, $frameworkPlugin)));
        return $smarty;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\WebResponse;

use Ray\Di\AbstractModule;
/**
 * Web response module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class HttpFoundationModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\\Sunday\\Extension\\WebResponse\\ResponseInterface')->to(__NAMESPACE__ . '\\HttpFoundation');
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ConsoleOutput;

use Ray\Di\AbstractModule;
/**
 * Output console module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ConsoleOutputModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\\Sunday\\Extension\\ConsoleOutput\\ConsoleOutputInterface')->to(__NAMESPACE__ . '\\ConsoleOutput');
    }
}
/**
 * This file is part of the BEAR.Packages package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Ray\Di\AbstractModule;
/**
 * Router module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class MinRouterModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->bind('BEAR\\Sunday\\Extension\\Router\\RouterInterface')->to(__NAMESPACE__ . '\\MinRouter');
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use Ray\Di\AbstractModule;
/**
 * Resource renderer module - PROD
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class TemplateEngineRendererModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\\Resource\\RenderInterface')->to(__NAMESPACE__ . '\\TemplateEngineRenderer');
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;
/**
 * Hal render module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class HalModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\\Resource\\RenderInterface')->to(__NAMESPACE__ . '\\HalRenderer')->in(Scope::SINGLETON);
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal;

use Ray\Di\AbstractModule;
use BEAR\Package\Module\Database\Dbal\Interceptor\TimeStamper;
use BEAR\Package\Module\Database\Dbal\Interceptor\Transactional;
/**
 * DBAL module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class DbalModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        // @Db
        $this->installDbInjector();
        // @Transactional
        $this->installTransaction();
        // @Time
        $this->installTimeStamper();
    }
    /**
     * @Db - db setter
     */
    private function installDbInjector()
    {
        $dbInjector = $this->requestInjection(__NAMESPACE__ . '\\Interceptor\\DbInjector');
        $this->bindInterceptor($this->matcher->annotatedWith('BEAR\\Sunday\\Annotation\\Db'), $this->matcher->startWith('on'), array($dbInjector));
    }
    /**
     * @Transactional - db transaction
     */
    private function installTransaction()
    {
        $this->bindInterceptor($this->matcher->any(), $this->matcher->annotatedWith('BEAR\\Sunday\\Annotation\\Transactional'), array(new Transactional()));
    }
    /**
     * @Time - put time to 'time' property
     */
    private function installTimeStamper()
    {
        $this->bindInterceptor($this->matcher->any(), $this->matcher->annotatedWith('BEAR\\Sunday\\Annotation\\Time'), array(new TimeStamper()));
    }
}
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Tag interface for Advice. Implementations can be any type of advice, such as Interceptors.
 *
 * @package  Ray.Aop
 * @link     http://aopalliance.sourceforge.net/doc/org/aopalliance/aop/Advice.html
 */
interface Advice
{
    
}
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * This interface represents a generic interceptor.
 *
 * This interface is not used directly. Use the the sub-interfaces to intercept specific events.
 *
 * @package Ray.Aop
 * @link    http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/Interceptor.html
 */
interface Interceptor extends Advice
{
    
}
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Intercepts calls on an interface on its way to the target. These are nested "on top" of the target.
 *
 * The user should implement the invoke(MethodInvocation) method to modify the original behavior.
 * E.g. the following class implements a tracing interceptor (traces all the calls on the intercepted method(s)):
 *
 * @package Ray.Aop
 * @link    http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html
 */
interface MethodInterceptor extends Interceptor
{
    /**
     * Implement this method to perform extra treatments before and after the invocation.
     *
     * Polite implementations would certainly like to invoke {@link Joinpoint#proceed()}.
     *
     * @param MethodInvocation $invocation the method invocation joinpoint
     *
     * @return mixed the result of the call to {@link
     * Joinpoint#proceed()}, might be intercepted by the
     * interceptor.
     */
    public function invoke(MethodInvocation $invocation);
}
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal\Interceptor;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Logging\SQLLogger;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * Cache interceptor
 *
 * @package    BEAR.Sunday
 * @subpackage Intercetor
 */
final class DbInjector implements MethodInterceptor
{
    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var DebugStack
     */
    private $sqlLogger;
    /**
     * DSN for master
     *
     * @var array
     */
    private $masterDb;
    /**
     * DSN for slave
     *
     * @var array
     */
    private $slaveDb;
    /**
     * Set annotation reader
     *
     * @param Reader $reader
     *
     * @return void
     * @Inject
     */
    public function setReader(Reader $reader)
    {
        $this->reader = $reader;
    }
    /**
     * Set SqlLogger
     *
     * @param \Doctrine\DBAL\Logging\SQLLogger $sqlLogger
     *
     * @Inject(optional = true)
     */
    public function setSqlLogger(SQLLogger $sqlLogger)
    {
        $this->sqlLogger = $sqlLogger;
    }
    /**
     * Constructor
     *
     * @param  array $masterDb
     * @@param array $slaveDb
     *
     * @Inject
     * @Named("masterDb=master_db,slaveDb=slave_db")
     */
    public function __construct(array $masterDb, array $slaveDb)
    {
        $this->masterDb = $masterDb;
        $this->slaveDb = $slaveDb;
    }
    /**
     * (non-PHPdoc)
     * @see Ray\Aop.MethodInterceptor::invoke()
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        $method = $invocation->getMethod();
        $connectionParams = $method->name === 'onGet' ? $this->slaveDb : $this->masterDb;
        $pagerAnnotation = $this->reader->getMethodAnnotation($method, 'BEAR\\Sunday\\Annotation\\DbPager');
        if ($pagerAnnotation) {
            $connectionParams['wrapperClass'] = 'BEAR\\Package\\Module\\Database\\Dbal\\PagerConnection';
            $db = DriverManager::getConnection($connectionParams);
            /** @var $db \BEAR\Package\Module\Database\Dbal\PagerConnection */
            $db->setMaxPerPage($pagerAnnotation->limit);
        } else {
            $db = DriverManager::getConnection($connectionParams);
        }
        /* @var $db \BEAR\Package\Module\Database\Dbal\PagerConnection */
        if ($this->sqlLogger instanceof SQLLogger) {
            $db->getConfiguration()->setSQLLogger($this->sqlLogger);
        }
        $object->setDb($db);
        $result = $invocation->proceed();
        if ($this->sqlLogger instanceof DebugStack) {
            $this->sqlLogger->stopQuery();
            $object->headers['x-sql'] = array($this->sqlLogger->queries);
        } elseif ($this->sqlLogger instanceof SQLLogger) {
            $this->sqlLogger->stopQuery();
        }
        if ($pagerAnnotation) {
            $pagerData = $db->getPager();
            if ($pagerData) {
                $object->headers['pager'] = $pagerData;
            }
        }
        return $result;
    }
}
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */
namespace Doctrine\DBAL\Logging;

/**
 * Interface for SQL loggers.
 *
 * 
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
interface SQLLogger
{
    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql The SQL to be executed.
     * @param array $params The SQL parameters.
     * @param array $types The SQL parameter types.
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null);
    /**
     * Mark the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery();
}
/**
 * This file is part of the Ray package.
 *
 * @package    Ray.Di
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

/**
 * Exception interface
 *
 * @package    Ray.Di
 * @subpackage Exception
 */
interface Exception
{
    
}
/**
 * This file is part of the Ray package.
 *
 * @package    Ray.Di
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

use LogicException;
/**
 * Invalid binding.
 *
 * @package    Ray.Di
 * @subpackage Exception
 */
class Binding extends LogicException implements Exception
{
    
}
/**
 * This file is part of the Ray package.
 *
 * @package    Ray.Di
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di\Exception;

/**
 *  Optional injection is not bound.
 *
 * @package    Ray.Di
 * @subpackage Exception
 */
class OptionalInjectionNotBound extends Binding implements Exception
{
    
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Annotation;

/**
 * Annotation
 *
 * @package BEAR.Sunday
 */
interface AnnotationInterface
{
    
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Annotation;

/**
 * Db
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @package    BEAR.Sunday
 * @subpackage Annotation
 */
final class Db implements AnnotationInterface
{
    
}
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Pointcut
 *
 * @package Ray.Di
 */
final class Pointcut
{
    /**
     * Class matcher
     *
     * @var Matcher
     */
    public $classMatcher;
    /**
     * Method matcher
     *
     * @var Matcher
     */
    public $methodMatcher;
    /**
     * Interceptors
     *
     * @var Interceptor[]
     */
    public $interceptors = array();
    /**
     * Constructor
     *
     * @param Matcher $classMatcher
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     */
    public function __construct(Matcher $classMatcher, Matcher $methodMatcher, array $interceptors)
    {
        $this->classMatcher = $classMatcher;
        $this->methodMatcher = $methodMatcher;
        $this->interceptors = $interceptors;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Annotation;

/**
 * Time
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @package    BEAR.Sunday
 * @subpackage Annotation
 */
final class Transactional implements AnnotationInterface
{
    
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use ReflectionProperty;
use Exception;
/**
 * Transaction interceptor
 *
 * @package    BEAR.Sunday
 * @subpackage Interceptor
 */
class Transactional implements MethodInterceptor
{
    /**
     * (non-PHPdoc)
     * @see Ray\Aop.MethodInterceptor::invoke()
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        $ref = new ReflectionProperty($object, 'db');
        $ref->setAccessible(true);
        $db = $ref->getValue($object);
        $db->beginTransaction();
        try {
            $invocation->proceed();
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
        }
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Annotation;

/**
 * Time
 *
 * @Annotation
 * @Target({"METHOD","CLASS"})
 *
 * @package    BEAR.Sunday
 * @subpackage Annotation
 */
final class Time implements AnnotationInterface
{
    
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
/**
 * Log Interceptor
 *
 * @package    BEAR.Sunday
 * @subpackage Interceptor
 */
class TimeStamper implements MethodInterceptor
{
    /**
     * (non-PHPdoc)
     * @see Ray\Aop.MethodInterceptor::invoke()
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        /** @noinspection PhpUndefinedFieldInspection */
        $object->time = date('Y-m-d H:i:s', time());
        $result = $invocation->proceed();
        return $result;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Log;

use Ray\Di\AbstractModule;
/**
 * Zf2 log module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ZfLogModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('Guzzle\\Log\\LogAdapterInterface')->toProvider('BEAR\\Package\\Module\\Log\\ZfLogModule\\ZfLogProvider');
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Log\ZfLogModule;

use BEAR\Sunday\Inject\LogDirInject;
use Guzzle\Log\Zf2LogAdapter;
use Ray\Di\ProviderInterface as Provide;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * Zend log provider
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ZfLogProvider implements Provide
{
    use LogDirInject;
    /**
     * Provide instance
     *
     * @return \Guzzle\Log\LogAdapterInterface
     */
    public function get()
    {
        $logger = new Logger();
        $writer = new Stream($this->logDir . '/app.log');
        $logger->addWriter($writer);
        return new Zf2LogAdapter($logger);
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\ExceptionHandle;

use Ray\Di\AbstractModule;
/**
 * Exception handle module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class HandleModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('')->annotatedWith('exceptionTpl')->toInstance('/Users/kooriyama/git/BEAR.Package/src/BEAR/Package/Module/ExceptionHandle' . '/template/view.php');
        $this->bind('BEAR\\Resource\\AbstractObject')->annotatedWith('errorPage')->to('BEAR\\Package\\Debug\\ExceptionHandle\\ErrorPage');
        $this->bind('BEAR\\Package\\Debug\\ExceptionHandle\\ExceptionHandlerInterface')->to('BEAR\\Package\\Debug\\ExceptionHandle\\ExceptionHandler');
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module;

use Ray\Di\AbstractModule;
/**
 * Scheme module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class SchemeModule extends AbstractModule
{
    /**
     * Scheme collection provider
     *
     * @var string
     */
    private $schemeProvider;
    /**
     * Constructor
     *
     * @param string $schemeProvider provider class name
     */
    public function __construct($schemeProvider)
    {
        $this->schemeProvider = $schemeProvider;
        parent::__construct();
    }
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\\Resource\\SchemeCollection')->toProvider($this->schemeProvider);
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Inject;

/**
 * Inject application namespace
 *
 * @package    BEAR.Sunday
 * @subpackage Inject
 */
trait AppNameInject
{
    /**
     * application namespace
     *
     * @var string
     */
    private $appName;
    /**
     * App name (=namespace) setter
     *
     * @param string $appName
     *
     * @Ray\Di\Di\Inject
     * @Ray\Di\Di\Named("app_name")
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Inject;

use Ray\Di\InjectorInterface as Di;
/**
 * Inject injector
 *
 * @package    BEAR.Sunday
 * @subpackage Inject
 */
trait InjectorInject
{
    /**
     * Dependency injector
     *
     * @var Di
     */
    private $injector;
    /**
     * Injector setter
     *
     * @param Di $injector
     *
     * @Ray\Di\Di\Inject
     */
    public function setInjector(Di $injector)
    {
        $this->injector = $injector;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Resource;

use Ray\Di\AbstractModule;
/**
 * Resource cache APC module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ApcModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('Guzzle\\Cache\\CacheAdapterInterface')->annotatedWith('resource_cache')->toProvider('BEAR\\Sunday\\Module\\Provider\\ApcCacheProvider');
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\WebContext;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;
/**
 * Aura.Web Context module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class AuraWebModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('Ray\\Di\\ProviderInterface')->annotatedWith('webContext')->to('BEAR\\Sunday\\Module\\Provider\\WebContextProvider')->in(Scope::SINGLETON);
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Cqrs;

use Ray\Di\AbstractModule;
/**
 * Cache module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class CacheModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $cacheLoader = $this->requestInjection(__NAMESPACE__ . '\\Interceptor\\CacheLoader');
        // bind @Cache annotated method in any class
        $this->bindInterceptor($this->matcher->any(), $this->matcher->annotatedWith('BEAR\\Sunday\\Annotation\\Cache'), array($cacheLoader));
        $cacheUpdater = $this->requestInjection(__NAMESPACE__ . '\\Interceptor\\CacheUpdater');
        $this->bindInterceptor($this->matcher->any(), $this->matcher->annotatedWith('BEAR\\Sunday\\Annotation\\CacheUpdate'), array($cacheUpdater));
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Cqrs\Interceptor;

use BEAR\Resource\AbstractObject as ResourceObject;
/**
 * Resource Links
 *
 * @package    BEAR.Sunday
 * @subpackage Page
 */
trait EtagTrait
{
    /**
     * @param $object
     * @param $args
     *
     * @return int
     */
    public function getEtag($object, $args)
    {
        $etag = crc32(get_class($object) . serialize($args));
        return $etag;
    }
    /**
     * Tagging
     *
     * @param ResourceObject $ro
     * @param string         $tag
     */
    public function tag(ResourceObject $ro, $tag)
    {
        $ro['headers']['etag'] = $tag;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Cqrs\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Guzzle\Cache\CacheAdapterInterface;
use Exception;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * Cache load interceptor
 *
 * @package    BEAR.Sunday
 * @subpackage Intercetor
 */
class CacheLoader implements MethodInterceptor
{
    use EtagTrait;
    /**
     * Cache header key
     *
     * @var string
     */
    const HEADER_CACHE = 'x-cache';
    /**
     * Constructor
     *
     * @param CacheAdapterInterface $cache
     *
     * @Inject
     * @Named("resource_cache")
     */
    public function __construct(CacheAdapterInterface $cache)
    {
        $this->cache = $cache;
    }
    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->getThis();
        $args = $invocation->getArguments();
        $id = $this->getEtag($ro, $args);
        $pager = isset($_GET['_start']) ? $_GET['_start'] : '';
        $saved = $this->cache->fetch($id);
        $pager = !$pager && isset($saved['pager']) ? 1 : $pager;
        if ($pager) {
            $pagered = isset($saved['pager'][$pager]) ? $saved['pager'][$pager] : false;
        } else {
            $pagered = $saved;
        }
        if ($pagered) {
            $resource = $invocation->getThis();
            list($resource->code, $resource->headers, $resource->body) = $pagered;
            $cache = json_decode($resource->headers[self::HEADER_CACHE], true);
            $resource->headers[self::HEADER_CACHE] = json_encode(array('mode' => 'R', 'date' => $cache['date'], 'life' => $cache['life']));
            return $resource;
        }
        $invocation->proceed();
        $resource = $invocation->getThis();
        $time = $invocation->getAnnotation()->time;
        $resource->headers[self::HEADER_CACHE] = json_encode(array('mode' => 'W', 'date' => date('r'), 'life' => $time));
        $data = array($resource->code, $resource->headers, $resource->body);
        if ($pager) {
            $saved['pager'][$pager] = $data;
            $data = $saved;
        }
        try {
            $this->cache->save($id, $data, $time);
        } catch (Exception $e) {
            error_log(get_class($e) . ':' . $e->getMessage());
        }
        return $resource;
    }
}
namespace Guzzle\Cache;

/**
 * Interface for cache adapters.
 *
 * Cache adapters allow Guzzle to utilize various frameworks for caching HTTP responses.
 *
 * @link http://www.doctrine-project.org/ Inspired by Doctrine 2
 */
interface CacheAdapterInterface
{
    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id      cache id The cache id of the entry to check for.
     * @param array  $options Array of cache adapter options
     *
     * @return bool Returns TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    public function contains($id, array $options = null);
    /**
     * Deletes a cache entry.
     *
     * @param string $id      cache id
     * @param array  $options Array of cache adapter options
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function delete($id, array $options = null);
    /**
     * Fetches an entry from the cache.
     *
     * @param string $id      cache id The id of the cache entry to fetch.
     * @param array  $options Array of cache adapter options
     *
     * @return string The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function fetch($id, array $options = null);
    /**
     * Puts data into the cache.
     *
     * @param string   $id       The cache id
     * @param string   $data     The cache entry/data
     * @param int|bool $lifeTime The lifetime. If != false, sets a specific lifetime for this cache entry
     * @param array    $options  Array of cache adapter options
     *
     * @return bool TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function save($id, $data, $lifeTime = false, array $options = null);
}
namespace Guzzle\Cache;

/**
 * Abstract cache adapter
 */
abstract class AbstractCacheAdapter implements CacheAdapterInterface
{
    protected $cache;
    /**
     * Get the object owned by the adapter
     *
     * @return mixed
     */
    public function getCacheObject()
    {
        return $this->cache;
    }
}
namespace Guzzle\Cache;

use Doctrine\Common\Cache\Cache;
/**
 * Doctrine 2 cache adapter
 *
 * @link http://www.doctrine-project.org/
 */
class DoctrineCacheAdapter extends AbstractCacheAdapter
{
    /**
     * DoctrineCacheAdapter
     *
     * @param Cache $cache Doctrine cache object
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
    /**
     * {@inheritdoc}
     */
    public function contains($id, array $options = null)
    {
        return $this->cache->contains($id);
    }
    /**
     * {@inheritdoc}
     */
    public function delete($id, array $options = null)
    {
        return $this->cache->delete($id);
    }
    /**
     * {@inheritdoc}
     */
    public function fetch($id, array $options = null)
    {
        return $this->cache->fetch($id);
    }
    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = false, array $options = null)
    {
        return $this->cache->save($id, $data, $lifeTime);
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Annotation;

/**
 * Cache
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @package    BEAR.Sunday
 * @subpackage Annotation
 */
final class Cache implements AnnotationInterface
{
    /**
     * Cache time
     *
     * @var integer
     */
    public $time = false;
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Module\Cqrs\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Guzzle\Cache\CacheAdapterInterface;
use ReflectionMethod;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * Cache update interceptor
 *
 * @package    BEAR.Sunday
 * @subpackage Intercetor
 */
class CacheUpdater implements MethodInterceptor
{
    use EtagTrait;
    /**
     * Constructor
     *
     * @param CacheAdapterInterface $cache
     *
     * @Inject
     * @Named("resource_cache")
     */
    public function __construct(CacheAdapterInterface $cache)
    {
        $this->cache = $cache;
    }
    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->getThis();
        // onGet(void) clear cache
        $id = $this->getEtag($ro, array(0 => null));
        $this->cache->delete($id);
        // onGet($id, $x, $y...) clear cache
        $getMethod = new ReflectionMethod($ro, 'onGet');
        $parameterNum = count($getMethod->getParameters());
        // cut as same size and order as onGet
        $slicedInvocationArgs = array_slice($invocation->getArguments(), 0, $parameterNum);
        $id = $this->getEtag($ro, $slicedInvocationArgs);
        $this->cache->delete($id);
        return $invocation->proceed();
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Annotation;

/**
 * CacheUpdate
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @package    BEAR.Sunday
 * @subpackage Annotation
 */
final class CacheUpdate implements AnnotationInterface
{
    
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Annotation;

/**
 * Form
 *
 * @Annotation
 * @Target({"METHOD"})
 *
 * @package    Sandbox
 * @subpackage Annotation
 */
final class Form
{
    
}
/**
 * This file is part of the Ray package.
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Bind;
/**
 * Defines the interface for dependency injector logger.
 *
 * @package Ray.Di
 */
interface LoggerInterface
{
    /**
     * Injection logger
     *
     * @param string $class
     * @param array  $params
     * @param array  $setter
     * @param object $object
     * @param Bind   $bind
     *
     * @return void
     */
    public function log($class, array $params, array $setter, $object, Bind $bind);
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Application;

use Ray\Aop\Bind;
use Ray\Di\LoggerInterface;
/**
 * Di logger
 */
class DiLogger implements LoggerInterface
{
    /**
     * @var string
     */
    private $logMessages = array();
    /**
     * log injection information
     *
     * @param string        $class
     * @param array         $params
     * @param array         $setter
     * @param object        $object
     * @param \Ray\Aop\Bind $bind
     */
    public function log($class, array $params, array $setter, $object, Bind $bind)
    {
        $toStr = function ($params) {
            foreach ($params as &$param) {
                if (is_object($param)) {
                    $param = get_class($param) . '#' . spl_object_hash($param);
                } elseif (is_scalar($param)) {
                    $param = '(' . gettype($param) . ') ' . (string) $param;
                } elseif (is_callable($param)) {
                    $param = '(Callable)';
                }
            }
            return implode(', ', $params);
        };
        $constructor = $toStr($params);
        $constructor = $constructor ? $constructor : '';
        $setter = $setter ? 'setter[' . implode(', ', array_keys($setter)) . ']' : '';
        $logMessage = "[DI] {$class} construct[{$constructor}] {$setter}";
        $this->logMessages[] = $logMessage;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return implode(PHP_EOL, $this->logMessages);
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Resource object marker interface
 *
 * @package BEAR.Resource
 */
interface ObjectInterface
{
    
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use ArrayIterator;
use Traversable;
/**
 * Trait for array access
 *
 * @package BEAR.Resource
 */
trait BodyArrayAccessTrait
{
    /**
     * Body
     *
     * @var mixed
     */
    public $body;
    /**
     * Returns the body value at the specified index
     *
     * @param mixed $offset offset
     *
     * @return mixed
     * @ignore
     */
    public function offsetGet($offset)
    {
        return $this->body[$offset];
    }
    /**
     * Sets the body value at the specified index to renew
     *
     * @param mixed $offset offset
     * @param mixed $value  value
     *
     * @return void
     * @ignore
     */
    public function offsetSet($offset, $value)
    {
        $this->body[$offset] = $value;
    }
    /**
     * Returns whether the requested index in body exists
     *
     * @param mixed $offset offset
     *
     * @return bool
     * @ignore
     */
    public function offsetExists($offset)
    {
        return isset($this->body[$offset]);
    }
    /**
     * Set the value at the specified index
     *
     * @param mixed $offset offset
     *
     * @return void
     * @ignore
     */
    public function offsetUnset($offset)
    {
        unset($this->body[$offset]);
    }
    /**
     * Get the number of public properties in the ArrayObject
     *
     * @return int
     */
    public function count()
    {
        return count($this->body);
    }
    /**
     * Sort the entries by key
     *
     * @return bool
     * @ignore
     */
    public function ksort()
    {
        return ksort($this->body);
    }
    /**
     * Sort the entries by key
     *
     * @return bool
     * @ignore
     */
    public function asort()
    {
        return asort($this->body);
    }
    /**
     * Get array iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return is_array($this->body) || $this->body instanceof Traversable ? new ArrayIterator($this->body) : new ArrayIterator(array());
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Exception;
/**
 * Trait for resource string
 *
 * @package BEAR.Resource
 */
trait RenderTrait
{
    /**
     * Renderer
     *
     * @var \BEAR\Resource\RenderInterface
     */
    protected $renderer;
    /**
     * Set renderer
     *
     * @param RenderInterface $renderer
     *
     * @return RenderTrait
     * @Inject(optional = true)
     */
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }
    /**
     * Return representational string
     *
     * Return object hash if representation renderer is not set.
     *
     * @return string
     */
    public function __toString()
    {
        /** @var $this AbstractObject */
        if (is_string($this->view)) {
            return $this->view;
        }
        if ($this->renderer instanceof RenderInterface) {
            try {
                $view = $this->renderer->render($this);
            } catch (Exception $e) {
                $view = '';
                error_log('Exception cached in ' . __METHOD__);
                error_log((string) $e);
            }
        } elseif (is_scalar($this->body)) {
            return (string) $this->body;
        } else {
            $view = '';
        }
        return $view;
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;
use ArrayAccess;
use Countable;
use IteratorAggregate;
/**
 * Abstract resource object
 *
 * @package BEAR.Resource
 */
abstract class AbstractObject implements ObjectInterface, ArrayAccess, Countable, IteratorAggregate
{
    // (array)
    use BodyArrayAccessTrait;
    // (string)
    use RenderTrait;
    /**
     * URI
     *
     * @var string
     */
    public $uri = '';
    /**
     * Resource status code
     *
     * @var int
     */
    public $code = 200;
    /**
     * Resource header
     *
     * @var array
     */
    public $headers = array();
    /**
     * Resource representation
     *
     * @var string
     */
    public $view;
    /**
     * Resource links
     *
     * @var array
     */
    public $links = array();
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Inject;

use BEAR\Resource\ResourceInterface;
/**
 * Inject resource client
 *
 * @package    BEAR.Sunday
 * @subpackage Inject
 */
trait ResourceInject
{
    /**
     * @var ResourceInterface
     */
    protected $resource;
    /**
     * Set resource
     *
     * @param ResourceInterface $resource
     *
     * @Ray\Di\Di\Inject
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension\Application;

/**
 * Interface for application context
 *
 * @package    BEAR.Sunday
 * @subpackage Application
 */
interface AppInterface
{
    
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Application;

use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface;
use BEAR\Sunday\Extension\WebResponse\ResponseInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Package\Debug\ExceptionHandle\ExceptionHandlerInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\AbstractObject as Page;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Inject;
/**
 * Application
 *
 * available run mode:
 *
 * 'Prod'
 * 'Api'
 * 'Dev'
 * 'Stab;
 * 'Test'
 *
 * @package BEAR.Package
 */
abstract class AbstractApp implements AppInterface
{
    /**
     * Dependency injector
     *
     * @var InjectorInterface
     */
    public $injector;
    /**
     * Resource client
     *
     * @var ResourceInterface
     */
    public $resource;
    /**
     * Response
     *
     * @var ResponseInterface
     */
    public $response;
    /**
     * Exception handler
     *
     * @var ExceptionHandlerInterface
     */
    public $exceptionHandler;
    /**
     * Router
     *
     * @var RouterInterface
     */
    public $router;
    /**
     * Resource logger
     *
     * @var ApplicationLoggerInterface
     */
    public $logger;
    /**
     * Response page object
     *
     * @var Page
     */
    public $page;
    /**
     * Constructor
     *
     * @param InjectorInterface          $injector         Dependency Injector
     * @param ResourceInterface          $resource         Resource client
     * @param ExceptionHandlerInterface  $exceptionHandler Exception handler
     * @param ApplicationLoggerInterface $logger           Application logger
     * @param ResponseInterface          $response         Web / Console response
     * @param RouterInterface            $router           URI Router
     *
     * @Inject
     */
    public function __construct(InjectorInterface $injector, ResourceInterface $resource, ExceptionHandlerInterface $exceptionHandler, ApplicationLoggerInterface $logger, ResponseInterface $response, RouterInterface $router)
    {
        $this->injector = $injector;
        $this->resource = $resource;
        $this->response = $response;
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger;
        $this->router = $router;
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\SignalHandler\HandleInterface;
use Ray\Di\Di\ImplementedBy;
/**
 * Interface for resource client
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Resource")
 */
interface ResourceInterface
{
    /**
     * Return new resource object instance
     *
     * @param string $uri
     *
     * @return self
     */
    public function newInstance($uri);
    /**
     * Set resource object
     *
     * @param mixed $ro
     *
     * @return AbstractObject
     */
    public function object($ro);
    /**
     * Set resource object created by URI.
     *
     * @param string $uri
     *
     * @return Resource
     */
    public function uri($uri);
    /**
     * Set named parameter query
     *
     * @param  array    $query
     *
     * @return Resource
     */
    public function withQuery(array $query);
    /**
     * Return Request
     *
     * @return mixed ( | Request)
     */
    public function request();
    /**
     * Link self
     *
     * @param string $linkKey
     *
     * @return mixed
     */
    public function linkSelf($linkKey);
    /**
     * Link new
     *
     * @param string $linkKey
     *
     * @return mixed
     */
    public function linkNew($linkKey);
    /**
     * Link crawl
     *
     * @param string $linkKey
     *
     * @return mixed
     */
    public function linkCrawl($linkKey);
    /**
     *  Attach argument provider
     *
     * @param  string                       $signal
     * @param SignalHandler\HandleInterface $argProvider
     *
     * @return mixed
     */
    public function attachParamProvider($signal, HandleInterface $argProvider);
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Debug\ExceptionHandle;

use Exception;
/**
 * Interface for exception handler
 *
 * @package BEAR.Package
 */
interface ExceptionHandlerInterface
{
    /**
     * Handle exception
     *
     * @param Exception $e
     */
    public function handle(Exception $e);
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension;

/**
 * Interface for application extension
 *
 * @package BEAR.Sunday
 */
interface ExtensionInterface
{
    
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension\ApplicationLogger;

use BEAR\Sunday\Extension\ExtensionInterface;
use BEAR\Resource\LoggerInterface as ResourceLoggerInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Di\Di\Inject;
/**
 * Extension interface for application logger
 *
 * @package BEAR.Sunday
 */
interface ApplicationLoggerInterface extends ExtensionInterface
{
    /**
     * Set resource logger
     *
     * @param ResourceLoggerInterface $resourceLogger
     *
     * @Inject
     */
    public function __construct(ResourceLoggerInterface $resourceLogger);
    /**
     * Register log function on shutdown
     *
     * called in bootstrap
     *
     * @param AppInterface $app
     */
    public function register(AppInterface $app);
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension\WebResponse;

use BEAR\Sunday\Extension\ExtensionInterface;
/**
 * Interface for http response
 *
 * @package    BEAR.Sunday
 * @subpackage Web
 */
interface ResponseInterface extends ExtensionInterface
{
    /**
     * @param $page
     *
     * @return mixed
     */
    public function setResource($page);
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension\Router;

/**
 * Interface for router
 *
 * @package    BEAR.Sunday
 * @subpackage Web
 */
interface RouterInterface
{
    /**
     * Set globals
     *
     * @param mixed $globals array | \ArrayAccess
     *
     * @return self
     */
    public function setGlobals($globals);
    /**
     * Set argv
     *
     * @param $argv array | \ArrayAccess
     *
     * @return mixed
     */
    public function setArgv($argv);
    /**
     * Match route
     *
     * @return array [$method, $pageUri, $query]
     */
    public function match();
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception;
use BEAR\Resource\Uri;
use Guzzle\Cache\CacheAdapterInterface;
use BEAR\Resource\SignalHandler\HandleInterface;
use Ray\Di\Di\Scope;
use SplObjectStorage;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * Resource client
 *
 * @package BEAR.Resource
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @Scope("singleton")
 */
class Resource implements ResourceInterface
{
    /**
     * Resource factory
     *
     * @var Factory
     */
    private $factory;
    /**
     * Resource request invoker
     *
     * @var Invoker
     */
    private $invoker;
    /**
     * Resource request
     *
     * @var Request
     */
    private $request;
    /**
     * Requests
     *
     * @var \SplObjectStorage
     */
    private $requests;
    /**
     * Cache
     *
     * @var CacheAdapterInterface
     */
    private $cache;
    /**
     * Set cache adapter
     *
     * @param CacheAdapterInterface $cache
     *
     * @Inject(optional = true)
     * @Named("resource_cache")
     */
    public function setCacheAdapter(CacheAdapterInterface $cache)
    {
        $this->cache = $cache;
    }
    /**
     * Set scheme collection
     *
     * @param SchemeCollection $scheme
     *
     * @Inject(optional = true)
     */
    public function setSchemeCollection(SchemeCollection $scheme)
    {
        $this->factory->setSchemeCollection($scheme);
    }
    /**
     * Constructor
     *
     * @param Factory          $factory resource object factory
     * @param InvokerInterface $invoker resource request invoker
     * @param Request          $request resource request
     *
     * @Inject
     */
    public function __construct(Factory $factory, InvokerInterface $invoker, Request $request)
    {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->newRequest = $request;
        $this->requests = new SplObjectStorage();
        $this->invoker->setResourceClient($this);
    }
    /**
     * {@inheritdoc}
     */
    public function newInstance($uri)
    {
        if (substr($uri, -1) === '/') {
            $uri .= 'index';
        }
        $useCache = $this->cache instanceof CacheAdapterInterface;
        if ($useCache === true) {
            $key = 'res-' . str_replace('/', '-', $uri);
            $cached = $this->cache->fetch($key);
            if ($cached) {
                return $cached;
            }
        }
        $instance = $this->factory->newInstance($uri);
        if ($useCache === true) {
            /** @noinspection PhpUndefinedVariableInspection */
            $this->cache->save($key, $instance);
        }
        return $instance;
    }
    /**
     * {@inheritdoc}
     */
    public function object($ro)
    {
        $this->request->ro = $ro;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function uri($uri)
    {
        if (is_string($uri)) {
            if (!$this->request) {
                throw new Exception\BadRequest('Request method (get/put/post/delete/options) required before uri()');
            }
            if (!preg_match('|^[a-z]+?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $uri)) {
                throw new Exception\Uri($uri);
            }
            // uri with query parsed
            if (strpos($uri, '?') !== false) {
                $parsed = parse_url($uri);
                $uri = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $query);
                    $this->withQuery($query);
                }
            }
            $this->request->ro = $this->newInstance($uri);
            $this->request->uri = $uri;
            return $this;
        }
        if ($uri instanceof Uri) {
            $this->request->ro = $this->newInstance($uri->uri);
            $this->withQuery($uri->query);
            return $this;
        }
        throw new Exception\Uri();
    }
    /**
     * {@inheritdoc}
     */
    public function withQuery(array $query)
    {
        $this->request->query = $query;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function addQuery(array $query)
    {
        $this->request->query = array_merge($this->request->query, $query);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function linkSelf($linkKey)
    {
        $link = new LinkType();
        $link->key = $linkKey;
        $link->type = LinkType::SELF_LINK;
        $this->request->links[] = $link;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function linkNew($linkKey)
    {
        $link = new LinkType();
        $link->key = $linkKey;
        $link->type = LinkType::NEW_LINK;
        $this->request->links[] = $link;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function linkCrawl($linkKey)
    {
        $link = new LinkType();
        $link->key = $linkKey;
        $link->type = LinkType::CRAWL_LINK;
        $this->request->links[] = $link;
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function request()
    {
        $this->request->ro->uri = $this->request->toUri();
        if (isset($this->request->options['sync'])) {
            $this->requests->attach($this->request);
            $this->request = clone $this->newRequest;
            return $this;
        }
        if ($this->request->in === 'eager') {
            if ($this->requests->count() === 0) {
                $result = $this->invoker->invoke($this->request);
            } else {
                $this->requests->attach($this->request);
                $result = $this->invoker->invokeSync($this->requests);
            }
            if (!$result instanceof ObjectInterface && isset($this->request->ro)) {
                $this->request->ro->body = $result;
                $result = $this->request->ro;
            }
            return $result;
        }
        // logs
        return $this->request;
    }
    /**
     * {@inheritdoc}
     */
    public function attachParamProvider($signal, HandleInterface $argProvider)
    {
        /** @noinspection PhpParamsInspection */
        $this->invoker->getSignal()->handler('\\BEAR\\Resource\\Invoker', \BEAR\Resource\Invoker::SIGNAL_PARAM . $signal, $argProvider);
    }
    /**
     * {@inheritdoc}
     * @throws Exception\Request
     */
    public function __get($name)
    {
        switch ($name) {
            case 'get':
            case 'post':
            case 'put':
            case 'delete':
            case 'head':
            case 'options':
                $this->request = clone $this->newRequest;
                $this->request->method = $name;
                return $this;
            case 'lazy':
            case 'eager':
                $this->request->in = $name;
                return $this;
            case 'poe':
            case 'csrf':
            case 'sync':
                $this->request->options[$name] = true;
                return $this;
            default:
                throw new Exception\BadRequest($name, 400);
        }
    }
    /**
     * Return request string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->request->toUri();
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Adapter\AdapterInterface;
use ArrayObject;
/**
 * Resource scheme collection
 *
 * @package BEAR.Resource
 */
class SchemeCollection extends ArrayObject
{
    /**
     * Scheme
     *
     * @var string
     */
    private $scheme;
    /**
     * Host
     *
     * @var string
     */
    private $host;
    /**
     * Set scheme
     *
     * @param $scheme
     *
     * @return SchemeCollection
     */
    public function scheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }
    /**
     * Set host
     *
     * @param $host
     *
     * @return SchemeCollection
     */
    public function host($host)
    {
        $this->host = $host;
        return $this;
    }
    /**
     * Set resource adapter
     *
     * @param AdapterInterface $adapter
     *
     * @return SchemeCollection
     */
    public function toAdapter(AdapterInterface $adapter)
    {
        $this[$this->scheme][$this->host] = $adapter;
        $this->scheme = $this->host = null;
        return $this;
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;
/**
 * Interface for resource factory
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("Factory")
 */
interface FactoryInterface
{
    /**
     * Return new resource object instance
     *
     * @param string $uri resource URI
     *
     * @return \BEAR\Resource\ObjectInterface;
     */
    public function newInstance($uri);
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
/**
 * Resource object factory
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Factory implements FactoryInterface
{
    /**
     * Resource adapter biding config
     *
     * @var SchemeCollection
     */
    private $scheme = array();
    /**
     * Constructor
     *
     * @param SchemeCollection  $scheme
     *
     * @Inject
     */
    public function __construct(SchemeCollection $scheme)
    {
        $this->scheme = $scheme;
    }
    /**
     * Set scheme collection
     *
     * @param SchemeCollection $scheme
     *
     * @Inject(optional = true)
     */
    public function setSchemeCollection(SchemeCollection $scheme)
    {
        $this->scheme = $scheme;
    }
    /**
     * {@inheritdoc}
     * @throws Exception\Scheme
     */
    public function newInstance($uri)
    {
        $parsedUrl = parse_url($uri);
        if (!(isset($parsedUrl['scheme']) && isset($parsedUrl['scheme']))) {
            throw new Exception\Uri();
        }
        $scheme = $parsedUrl['scheme'];
        $host = $parsedUrl['host'];
        if (!isset($this->scheme[$scheme])) {
            throw new Exception\Scheme($uri);
        }
        if (!isset($this->scheme[$scheme][$host])) {
            if (!isset($this->scheme[$scheme]['*'])) {
                throw new Exception\Scheme($uri);
            }
            $host = '*';
        }
        try {
            $adapter = $this->scheme[$scheme][$host];
            if ($adapter instanceof ProviderInterface) {
                $adapter = $adapter->get($uri);
            }
        } catch (\Exception $e) {
            throw new Exception\ResourceNotFound($uri, 0, $e);
        }
        $adapter->uri = $uri;
        return $adapter;
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;
/**
 * Resource request invoke interface
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Invoker")
 */
interface InvokerInterface
{
    /**
     * Invoke resource request
     *
     * @param  Request $request
     *
     * @return AbstractObject
     */
    public function invoke(Request $request);
    /**
     * Invoke traversal
     *
     * invoke callable
     *
     * @param \Traversable $requests
     */
    public function invokeTraversal(\Traversable $requests);
    /**
     * Invoke Sync
     *
     * @param \SplObjectStorage $requests
     *
     * @return mixed
     */
    public function invokeSync(\SplObjectStorage $requests);
    /**
     * Set resource client
     *
     * @param ResourceInterface $resource
     */
    public function setResourceClient(ResourceInterface $resource);
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;
/**
 * Interface for resource client
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Request")
 *
 */
interface RequestInterface
{
    /**
     * Constructor
     *
     * @param InvokerInterface $invoker
     *
     * @Inject
     */
    public function __construct(InvokerInterface $invoker);
    /**
     * InvokerInterface resource request
     *
     * @param array $query
     *
     * @return AbstractObject
     */
    public function __invoke(array $query = null);
    /**
     * To Request URI string
     *
     * @return string
     */
    public function toUri();
    /**
     * To Request URI string with request method
     *
     * @return string
     */
    public function toUriWithMethod();
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use OutOfBoundsException;
use Traversable;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
/**
 * Interface for resource adapter provider.
 *
 * @package BEAR.Resource
 *
 * @Scope("prototype")
 */
final class Request implements RequestInterface, ArrayAccess, IteratorAggregate
{
    use BodyArrayAccessTrait;
    /**
     * object URI scheme
     *
     * @var string
     */
    const SCHEME_OBJECT = 'object';
    /**
     * URI
     *
     * @var string
     */
    public $uri;
    /**
     * Resource object
     *
     * @var \BEAR\Resource\AbstractObject
     */
    public $ro;
    /**
     * Method
     *
     * @var string
     */
    public $method = '';
    /**
     * Query
     *
     * @var array
     */
    public $query = array();
    /**
     * Options
     *
     * @var array
     */
    public $options = array();
    /**
     * Request option (eager or lazy)
     *
     * @var string
     */
    public $in;
    /**
     * Links
     *
     * @var array
     */
    public $links = array();
    /**
     * Request Result
     *
     * @var Object
     */
    private $result;
    /**
     * {@inheritdoc}
     *
     * @Inject
     */
    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }
    /**
     * Set
     *
     * @param AbstractObject $ro
     * @param string         $uri
     * @param string         $method
     * @param array          $query
     */
    public function set(AbstractObject $ro, $uri, $method, array $query)
    {
        $this->ro = $ro;
        $this->uri = $uri;
        $this->method = $method;
        $this->query = $query;
    }
    /**
     * {@inheritdoc}
     */
    public function __invoke(array $query = null)
    {
        if (!is_null($query)) {
            $this->query = array_merge($this->query, $query);
        }
        $result = $this->invoker->invoke($this);
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function toUri()
    {
        $query = http_build_query($this->query, null, '&', PHP_QUERY_RFC3986);
        $uri = isset($this->ro->uri) && $this->ro->uri ? $this->ro->uri : $this->uri;
        if (isset(parse_url($uri)['query'])) {
            $queryString = $uri;
        } else {
            $queryString = "{$uri}" . ($query ? '?' : '') . $query;
        }
        return $queryString;
    }
    /**
     * {@inheritdoc}
     */
    public function toUriWithMethod()
    {
        return "{$this->method} " . $this->toUri();
    }
    /**
     * Render view
     *
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->result)) {
            $this->result = $this->__invoke();
        }
        return (string) $this->result;
    }
    /**
     * Returns the body value at the specified index
     *
     * @param mixed $offset offset
     *
     * @return mixed
     * @throws OutOfBoundsException
     */
    public function offsetGet($offset)
    {
        if (is_null($this->result)) {
            $this->result = $this->__invoke();
        }
        if (!isset($this->result->body[$offset])) {
            throw new OutOfBoundsException("[{$offset}] for object[" . get_class($this->result) . ']');
        }
        return $this->result->body[$offset];
    }
    /**
     * Returns whether the requested index in body exists
     *
     * @param mixed $offset offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_null($this->result)) {
            $this->result = $this->__invoke();
        }
        return isset($this->result->body[$offset]);
    }
    /**
     * Get array iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        if (is_null($this->result)) {
            $this->result = $this->__invoke();
        }
        $isArray = is_array($this->result->body) || $this->result->body instanceof Traversable;
        $iterator = $isArray ? new ArrayIterator($this->result->body) : new ArrayIterator(array());
        return $iterator;
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for resource adapter provider.
 *
 * @package BEAR.Resource
 */
interface ProviderInterface
{
    /**
     * Get resource adapter
     *
     * @param string $uri
     */
    public function get($uri);
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

/**
 * Interface for resource adapter
 */
interface AdapterInterface
{
    
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use BEAR\Resource\ObjectInterface;
use BEAR\Resource\ProviderInterface;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
use RuntimeException;
/**
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 *
 * @Scope("prototype")
 */
class App implements ObjectInterface, ProviderInterface, AdapterInterface
{
    /**
     * Application dependency injector
     *
     * @var \Ray\Di\Injector
     */
    private $injector;
    /**
     * Resource adapter namespace
     *
     * @var array
     */
    private $namespace;
    /**
     * Resource adapter path
     *
     * @var array
     */
    private $path;
    /**
     * Constructor
     *
     * @param InjectorInterface $injector  Application dependency injector
     * @param string            $namespace Resource adapter namespace
     * @param string            $path      Resource adapter path
     *
     * @Inject
     * @throws RuntimeException
     */
    public function __construct(InjectorInterface $injector, $namespace, $path)
    {
        if (!is_string($namespace)) {
            throw new RuntimeException('namespace not string');
        }
        $this->injector = $injector;
        $this->namespace = $namespace;
        $this->path = $path;
    }
    /**
     * (non-PHPdoc)
     *
     * @see    BEAR\Resource.ProviderInterface::get()
     */
    public function get($uri)
    {
        $parsedUrl = parse_url($uri);
        $path = str_replace('/', ' ', $parsedUrl['path']);
        $path = ucwords($path);
        $path = str_replace(' ', '\\', $path);
        $className = "{$this->namespace}\\{$this->path}{$path}";
        $instance = $this->injector->getInstance($className);
        return $instance;
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Di\ConfigInterface;
use Aura\Signal\Manager as Signal;
use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Annotation\ParamSignal;
use BEAR\Resource\Exception\MethodNotAllowed;
use Ray\Aop\Weave;
use Ray\Aop\ReflectiveMethodInvocation;
use ReflectionParameter;
use Ray\Di\Di\Scope;
use Ray\Di\Config;
use Ray\Di\Definition;
use ReflectionException;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * Resource request invoker
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Invoker implements InvokerInterface
{
    /**
     * Config
     *
     * @var \Ray\Di\Config
     */
    private $config;
    /**
     * @var \BEAR\Resource\Linker
     */
    private $linker;
    /**
     * @var \Aura\Signal\Manager
     */
    private $signal;
    /**
     * Logger
     *
     * @var \BEAR\Resource\Logger
     */
    private $logger;
    /**
     * Method OPTIONS
     *
     * @var string
     */
    const OPTIONS = 'options';
    /**
     * ProviderInterface annotation
     *
     * @var string
     */
    const ANNOTATION_PROVIDES = 'Provides';
    const SIGNAL_PARAM = 'param';
    /**
     * Constructor
     *
     * @param \Aura\Di\ConfigInterface $config
     * @param LinkerInterface          $linker
     * @param \Aura\Signal\Manager     $signal
     *
     * @Inject
     */
    public function __construct(ConfigInterface $config, LinkerInterface $linker, Signal $signal)
    {
        $this->config = $config;
        $this->linker = $linker;
        $this->signal = $signal;
    }
    /**
     * {@inheritdoc}
     */
    public function setResourceClient(ResourceInterface $resource)
    {
        $this->linker->setResource($resource);
    }
    /**
     * Resource logger setter
     *
     * @param LoggerInterface $logger
     *
     * @Inject(optional=true)
     */
    public function setResourceLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Return config
     *
     * @return \Ray\Di\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
     * {@inheritdoc}
     * @throws Exception\Request
     */
    public function invoke(Request $request)
    {
        $method = 'on' . ucfirst($request->method);
        $isWeave = $request->ro instanceof Weave;
        if ($isWeave && $request->method !== Invoker::OPTIONS) {
            $weave = $request->ro;
            /** @noinspection PhpUnusedLocalVariableInspection */
            /** @var $weave Callable */
            $result = $weave(array($this, 'getParams'), $method, $request->query);
            goto completed;
        }
        /** @var $request->ro \Ray\Aop\Weave */
        /** @noinspection PhpUndefinedMethodInspection */
        $ro = $isWeave ? $request->ro->___getObject() : $request->ro;
        if (method_exists($ro, $method) !== true) {
            if ($request->method === self::OPTIONS) {
                $options = $this->getOptions($ro);
                $ro->headers['allow'] = $options['allow'];
                $ro->headers += $options['params'];
                $ro->body = null;
                return $ro;
            }
            throw new Exception\MethodNotAllowed(get_class($request->ro) . "::{$method}()", 405);
        }
        $params = $this->getParams($request->ro, $method, $request->query);
        try {
            $result = call_user_func_array(array($request->ro, $method), $params);
        } catch (\Exception $e) {
            // @todo implements "Exception signal"
            throw $e;
        }
        // link
        completed:
        if ($request->links) {
            $result = $this->linker->invoke($request->ro, $request, $result);
        }
        if (!$result instanceof AbstractObject) {
            $request->ro->body = $result;
            $result = $request->ro;
            if ($result instanceof Weave) {
                $result = $result->___getObject();
            }
        }
        // request / result log
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($request, $result);
        }
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function invokeTraversal(\Traversable $requests)
    {
        foreach ($requests as &$element) {
            if ($element instanceof Request || is_callable($element)) {
                $element = $element();
            }
        }
        return $requests;
    }
    /**
     * Get named parameters
     *
     * @param object $object
     * @param string $method
     * @param array  $args
     *
     * @throws Exception\MethodNotAllowed
     * @return array
     */
    public function getParams($object, $method, array $args)
    {
        try {
            $parameters = (new \ReflectionMethod($object, $method))->getParameters();
        } catch (ReflectionException $e) {
            throw new MethodNotAllowed();
        }
        if ($parameters === array()) {
            return array();
        }
        $providesArgs = array();
        $params = array();
        foreach ($parameters as $parameter) {
            /** @var $parameter \ReflectionParameter */
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $params[] = $parameter->getDefaultValue();
            } elseif (isset($providesArgs[$parameter->name])) {
                $params[] = $providesArgs[$parameter->name];
            } else {
                $result = $this->getArgumentBySignal($parameter, $object, $method, $args);
                if ($result->args) {
                    $providesArgs = $result->args;
                }
                $params[] = $result->value;
            }
        }
        return $params;
    }
    /**
     * Return argument from provider or signal handler
     *
     * This method called when client and service object both has sufficient argument
     *
     * @param \ReflectionParameter $parameter
     * @param  object              $object
     * @param string               $method
     * @param array                $args
     *
     * @return Result
     * @throws Exception\Parameter
     */
    private function getArgumentBySignal(ReflectionParameter $parameter, $object, $method, array $args)
    {
        $definition = $this->config->fetch(get_class($object))[Config::INDEX_DEFINITION];
        /** @var $definition \Ray\Di\Definition */
        $userAnnotation = $definition->getUserAnnotationByMethod($method);
        $signalAnnotations = isset($userAnnotation['ParamSignal']) ? $userAnnotation['ParamSignal'] : array();
        $signalIds = array('Provides');
        foreach ($signalAnnotations as $annotation) {
            if ($annotation instanceof ParamSignal) {
                $signalIds[] = $annotation->value;
            }
        }
        $return = new Result();
        if (!$signalIds) {
            goto PARAMETER_NOT_PROVIDED;
        }
        foreach ($signalIds as $signalId) {
            $results = $this->signal->send($this, self::SIGNAL_PARAM . $signalId, $return, $parameter, new ReflectiveMethodInvocation(array($object, $method), $args, $signalAnnotations), $definition);
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $isStopped = $results->isStopped();
        $result = $results->getLast();
        if ($isStopped === false || is_null($result)) {
            goto PARAMETER_NOT_PROVIDED;
        }
        PARAMETER_PROVIDED:
        return $return;
        PARAMETER_NOT_PROVIDED:
        /** @noinspection PhpUnreachableStatementInspection */
        $msg = '$' . "{$parameter->name} in " . get_class($object) . '::' . $method;
        throw new Exception\Parameter($msg);
    }
    /**
     * Return available resource request method
     *
     * @param ResourceObject $ro
     *
     * @return array
     */
    protected function getOptions(ResourceObject $ro)
    {
        $ref = new \ReflectionClass($ro);
        $methods = $ref->getMethods();
        $allow = array();
        foreach ($methods as $method) {
            $isRequestMethod = substr($method->name, 0, 2) === 'on' && substr($method->name, 0, 6) !== 'onLink';
            if ($isRequestMethod) {
                $allow[] = strtolower(substr($method->name, 2));
            }
        }
        $params = array();
        foreach ($allow as $method) {
            $refMethod = new \ReflectionMethod($ro, 'on' . $method);
            $parameters = $refMethod->getParameters();
            $paramArray = array();
            foreach ($parameters as $parameter) {
                $name = $parameter->getName();
                $param = $parameter->isOptional() ? "({$name})" : $name;
                $paramArray[] = $param;
            }
            $key = "param-{$method}";
            $params[$key] = implode(',', $paramArray);
        }
        $result = array('allow' => $allow, 'params' => $params);
        return $result;
    }
    /**
     * {@inheritdoc}
     */
    public function invokeSync(\SplObjectStorage $requests)
    {
        $requests->rewind();
        $data = new \ArrayObject();
        while ($requests->valid()) {
            // each sync request method call.
            $request = $requests->current();
            if (method_exists($request->ro, 'onSync')) {
                call_user_func(array($request->ro, 'onSync'), $request, $data);
            }
            $requests->next();
        }
        // onFinalSync summarize all sync request data.
        /** @noinspection PhpUndefinedVariableInspection */
        $result = call_user_func(array($request->ro, 'onFinalSync'), $request, $data);
        return $result;
    }
    /**
     * Return signal manager
     *
     * @return \Aura\Signal\Manager
     */
    public function getSignal()
    {
        return $this->signal;
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\AbstractObject as ResourceObject;
use Ray\Di\Di\ImplementedBy;
/**
 * Interface for resource link
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Linker")
 */
interface LinkerInterface
{
    /**
     * InvokerInterface link
     *
     * @param ResourceObject  $ro
     * @param Request         $request
     * @param mixed           $linkValue
     *
     * @return mixed
     */
    public function invoke(ResourceObject $ro, Request $request, $linkValue);
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Signal
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Signal;

/**
 * 
 * Processes signals through to Handler objects.
 * 
 * @package Aura.Signal
 * 
 */
class Manager
{
    /**
     * 
     * Indicates that the signal should not call more Handler instances.
     * 
     * @const string
     * 
     */
    const STOP = 'Aura\\Signal\\Manager::STOP';
    /**
     * 
     * A factory to create Handler objects.
     * 
     * @var HandlerFactory
     * 
     */
    protected $handler_factory;
    /**
     * 
     * An array of Handler instances that respond to class signals.
     * 
     * @var array
     * 
     */
    protected $handlers = array();
    /**
     * 
     * A prototype ResultCollection; this will be cloned by `send()` to retain
     * the Result objects from Handler instances.
     * 
     * @var ResultCollection
     * 
     */
    protected $result_collection;
    /**
     * 
     * A factory to create Result objects.
     * 
     * @var ResultFactory
     * 
     */
    protected $result_factory;
    /**
     * 
     * A ResultCollection from the last signal sent.
     * 
     * @var ResultCollection
     * 
     */
    protected $results;
    /**
     * 
     * Have the handlers for a signal been sorted by position?
     * 
     * @var array
     * 
     */
    protected $sorted = array();
    /**
     * 
     * Constructor.
     * 
     * @param HandlerFactory $handler_factory A factory to create Handler 
     * objects.
     * 
     * @param ResultFactory $result_factory A factory to create Result objects.
     * 
     * @param ResultCollection $result_collection A prototype ResultCollection.
     * 
     * @param array $handlers An array describing Handler params.
     * 
     */
    public function __construct(HandlerFactory $handler_factory, ResultFactory $result_factory, ResultCollection $result_collection, array $handlers = array())
    {
        $this->handler_factory = $handler_factory;
        $this->result_factory = $result_factory;
        $this->result_collection = $result_collection;
        foreach ($handlers as $handler) {
            list($sender, $signal, $callback) = $handler;
            if (isset($handler[3])) {
                $position = $handler[3];
            } else {
                $position = 5000;
            }
            $this->handler($sender, $signal, $callback, $position);
        }
        $this->results = clone $this->result_collection;
    }
    /**
     * 
     * Adds a Handler to respond to a sender signal.
     * 
     * @param string|object $sender The class or object sender of the signal.
     * If a class, inheritance will be honored, and '*' will be interpreted
     * as "any class."
     * 
     * @param string $signal The name of the signal for that sender.
     * 
     * @param callback The callback to execute when the signal is received.
     * 
     * @param int $position The handler processing position; lower numbers are
     * processed first. Use this to force a handler to be used before or after
     * others.
     * 
     * @return void
     * 
     */
    public function handler($sender, $signal, $callback, $position = 5000)
    {
        $handler = $this->handler_factory->newInstance(array('sender' => $sender, 'signal' => $signal, 'callback' => $callback));
        $this->handlers[$signal][(int) $position][] = $handler;
        $this->sorted[$signal] = false;
    }
    /**
     * 
     * Gets Handler instances for the Manager.
     * 
     * @param string $signal Only get Handler instances for this signal; if 
     * null, get all Handler instances.
     * 
     * @return array
     * 
     */
    public function getHandlers($signal = null)
    {
        if (!$signal) {
            return $this->handlers;
        }
        if (!isset($this->handlers[$signal])) {
            return;
        }
        if (!$this->sorted[$signal]) {
            ksort($this->handlers[$signal]);
        }
        return $this->handlers[$signal];
    }
    /**
     * 
     * Invokes the Handler objects for a sender and signal.
     * 
     * @param object $origin The object sending the signal. Note that this is
     * always an object, not a class name.
     * 
     * @param string $signal The name of the signal from that origin.
     * 
     * @params Arguments to pass to the Handler callback.
     * 
     * @return ResultCollection The results from each of the Handler objects.
     * 
     */
    public function send($origin, $signal)
    {
        // clone a new result collection
        $this->results = clone $this->result_collection;
        // get the arguments to be passed to the handler
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        // now process the signal through the handlers and return the results
        $this->process($origin, $signal, $args);
        return $this->results;
    }
    /**
     * 
     * Invokes the Handler objects for a sender and signal.
     * 
     * @param object $origin The object sending the signal. Note that this is
     * always an object, not a class name.
     * 
     * @param string $signal The name of the signal from that origin.
     * 
     * @param $args Arguments to pass to the Handler callback.
     * 
     */
    protected function process($origin, $signal, $args)
    {
        // are there any handlers for this signal, regardless of sender?
        $list = $this->getHandlers($signal);
        if (!$list) {
            return;
        }
        // go through the handler positions for the signal
        foreach ($list as $position => $handlers) {
            // go through each handler in this position
            foreach ($handlers as $handler) {
                // try the handler
                $params = $handler->exec($origin, $signal, $args);
                // if it executed, it returned the params for a Result object
                if ($params) {
                    // create a Result object
                    $result = $this->result_factory->newInstance($params);
                    // allow a meta-handler to examine the Result object,
                    // but only if it wasn't sent from the Manager (this
                    // prevents infinite looping). use process() instead
                    // of send() to prevent resetting the $results prop.
                    if ($origin !== $this) {
                        $this->process($this, 'handler_result', array($result));
                    }
                    // retain the result
                    $this->results->append($result);
                    // should we stop processing?
                    if ($result->value === static::STOP) {
                        // yes, leave the processing loop
                        return;
                    }
                }
            }
        }
    }
    /**
     * 
     * Returns the ResultCollection from the last signal processing.
     * 
     * @return ResultCollection
     * 
     */
    public function getResults()
    {
        return $this->results;
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use IteratorAggregate;
use BEAR\Resource\AbstractObject as ResourceObject;
/**
 * Interface for resource logger
 *
 * @package BEAR.Resource
 */
interface LoggerInterface extends IteratorAggregate
{
    /**
     * Log
     *
     * @param RequestInterface $request
     * @param ResourceObject   $result
     *
     * @return void
     */
    public function log(RequestInterface $request, ResourceObject $result);
    /**
     * Set log writer
     *
     * @param LogWriterInterface $writer
     *
     * @return void
     */
    public function setWriter(LogWriterInterface $writer);
    /**
     * write log
     *
     * @return void
     */
    public function write();
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Annotation\Link as AnnotationLink;
use BEAR\Resource\Exception\BadLinkRequest;
use Ray\Di\Di\Scope;
use Doctrine\Common\Annotations\Reader;
use SplQueue;
use ReflectionMethod;
use Ray\Di\Di\Inject;
/**
 * Resource linker
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
final class Linker implements LinkerInterface
{
    /**
     * Method name
     *
     * @var string
     */
    private $method;
    /**
     * Resource client
     *
     * @var ResourceInterface
     */
    private $resource;
    /**
     * Set resource
     *
     * @param $resource $resource
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }
    /**
     * Constructor
     *
     * @param \Doctrine\Common\Annotations\Reader $reader
     *
     * @Inject
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }
    /**
     * {@inheritdoc}
     * @throws Exception\Link
     */
    public function invoke(ResourceObject $ro, Request $request, $sourceValue)
    {
        $this->method = 'on' . ucfirst($request->method);
        $links = $request->links;
        $hasTargeted = false;
        $refValue =& $sourceValue;
        $q = new SplQueue();
        $q->setIteratorMode(\SplQueue::IT_MODE_DELETE);
        // has links
        foreach ($links as $link) {
            $cnt = $q->count();
            if ($cnt !== 0) {
                for ($i = 0; $i < $cnt; $i++) {
                    list($item, $ro) = $q->dequeue();
                    $request = $this->getLinkResult($ro, $link->key, (array) $item);
                    if (!$request instanceof Request) {
                        throw new Exception\Link('From list to instance link is not currently supported.');
                    }
                    $ro = $request->ro;
                    $requestResult = $request();
                    /** @var $requestResult AbstractObject */
                    $a = $requestResult->body;
                    $item[$link->key] = $a;
                    $item = (array) $item;
                }
                continue;
            }
            if ($this->isList($refValue)) {
                foreach ($refValue as &$item) {
                    $request = $this->getLinkResult($ro, $link->key, $item);
                    /** @noinspection PhpUndefinedFieldInspection */
                    $requestResult = is_callable($request) ? $request()->body : $request;
                    $requestResult = is_array($requestResult) ? new \ArrayObject($requestResult) : $requestResult;
                    $item[$link->key] = $requestResult;
                    $q->enqueue(array($requestResult, $request->ro));
                }
                $refValue =& $requestResult;
                continue;
            }
            $request = $this->getLinkResult($ro, $link->key, $refValue);
            if (!$request instanceof Request) {
                return $request;
            }
            $ro = $request->ro;
            $requestResult = $request();
            switch ($link->type) {
                case LinkType::NEW_LINK:
                    if (!$hasTargeted) {
                        $sourceValue = array($sourceValue, $requestResult->body);
                        $hasTargeted = true;
                    } else {
                        $sourceValue[] = $requestResult->body;
                    }
                    $refValue =& $requestResult;
                    break;
                case LinkType::CRAWL_LINK:
                    $refValue[$link->key] = $requestResult->body;
                    $refValue =& $requestResult;
                    break;
                case LinkType::SELF_LINK:
                default:
                    $refValue = $requestResult->body;
            }
        }
        array_walk_recursive($sourceValue, function (&$in) {
            if ($in instanceof \ArrayObject) {
                $in = (array) $in;
            }
        });
        return $sourceValue;
    }
    /**
     * Call link method
     *
     * @param mixed  $ro
     * @param string $linkKey
     * @param mixed  $input
     *
     * @return mixed
     * @throws BadLinkRequest
     */
    private function getLinkResult($ro, $linkKey, $input)
    {
        $method = 'onLink' . ucfirst($linkKey);
        if (!method_exists($ro, $method)) {
            $annotations = $this->reader->getMethodAnnotations(new ReflectionMethod($ro, $this->method));
            foreach ($annotations as $annotation) {
                if ($annotation instanceof AnnotationLink) {
                    if ($annotation->rel === $linkKey) {
                        $uri = $annotation->href;
                    }
                    $method = $annotation->method;
                    if ($input instanceof AbstractObject) {
                        $input = $input->body;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    /** @noinspection PhpUndefinedVariableInspection */
                    $result = $this->resource->{$method}->uri($uri)->withQuery($input)->eager->request();
                    return $result;
                }
            }
            throw new BadLinkRequest(get_class($ro) . "::{$method}");
        }
        if (!$input instanceof AbstractObject) {
            $ro->body = $input;
            $input = $ro;
        }
        $result = call_user_func(array($ro, $method), $input);
        return $result;
    }
    /**
     * Is data list ?
     *
     * @param mixed $list
     *
     * @return boolean
     */
    private function isList($list)
    {
        if (!is_array($list)) {
            return false;
        }
        $list = array_values((array) $list);
        $result = count($list) > 1 && isset($list[0]) && isset($list[1]) && is_array($list[0]) && is_array($list[1]) && array_keys($list[0]) === array_keys($list[1]);
        return $result;
    }
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Signal
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Signal;

/**
 * 
 * A factory to create Handler objects.
 * 
 * @package Aura.Signal
 * 
 */
class HandlerFactory
{
    /**
     * 
     * An array of default parameters for Handler objects.
     * 
     * @var array
     * 
     */
    protected $params = array('sender' => null, 'signal' => null, 'callback' => null);
    /**
     * 
     * Creates and returns a new Handler object.
     * 
     * @param array $params An array of key-value pairs corresponding to
     * Handler constructor params.
     * 
     * @return Handler
     * 
     */
    public function newInstance(array $params)
    {
        $params = array_merge($this->params, $params);
        return new Handler($params['sender'], $params['signal'], $params['callback']);
    }
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Signal
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Signal;

/**
 * 
 * A factory to create Result objects.
 * 
 * @package Aura.Signal
 * 
 */
class ResultFactory
{
    /**
     * 
     * An array of default parameters for Result objects.
     * 
     * @var array
     * 
     */
    protected $params = array('origin' => null, 'sender' => null, 'signal' => null, 'value' => null);
    /**
     * 
     * Creates and returns a new Option object.
     * 
     * @param array $params An array of key-value pairs corresponding to
     * Result constructor params.
     * 
     * @return Result
     * 
     */
    public function newInstance(array $params)
    {
        $params = array_merge($this->params, $params);
        return new Result($params['origin'], $params['sender'], $params['signal'], $params['value']);
    }
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Signal
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Signal;

/**
 * 
 * Represents a collection of Result objects.
 * 
 * @package Aura.Signal
 * 
 */
class ResultCollection extends \ArrayObject
{
    /**
     * 
     * override to avoid problems with Forge::newInstance() throwing
     * Fatal error: Uncaught exception 'InvalidArgumentException'
     * with message 'Passed variable is not an array or object, using 
     * empty array instead' in 
     * ~/system/package/Aura.Di/src/Aura/Di/Forge.php on line 103
     * 
     */
    public function __construct()
    {
        parent::__construct(array());
    }
    /**
     * 
     * Returns the last Result in the collection.
     * 
     * @return Result
     * 
     */
    public function getLast()
    {
        $k = count($this);
        if ($k > 0) {
            return $this[$k - 1];
        }
    }
    /**
     * 
     * Tells if the ResultCollection was stopped during processing.
     * 
     * @return bool
     * 
     */
    public function isStopped()
    {
        $last = $this->getLast();
        if ($last) {
            return $last->value === Manager::STOP;
        }
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use ArrayIterator;
use Countable;
use Ray\Di\Di\Scope;
use BEAR\Resource\AbstractObject as ResourceObject;
use Ray\Di\Di\Inject;
/**
 * Interface for resource logger
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Logger implements LoggerInterface, Countable
{
    const LOG_REQUEST = 0;
    const LOG_RESULT = 1;
    /**
     * Logs
     *
     * @var array
     */
    private $logs = array();
    /**
     * @var LogWriterInterface
     */
    private $writer;
    /**
     * Return new resource object instance
     *
     * {@inheritdoc}
     */
    public function log(RequestInterface $request, ResourceObject $result)
    {
        $this->logs[] = array(self::LOG_REQUEST => $request, self::LOG_RESULT => $result);
    }
    /**
     * {@inheritdoc}
     *
     * @Inject(optional = true)
     */
    public function setWriter(LogWriterInterface $writer)
    {
        $this->writer = $writer;
    }
    /**
     * {@inheritdoc}
     */
    public function write()
    {
        if ($this->writer instanceof LogWriterInterface) {
            foreach ($this->logs as $log) {
                $this->writer->write($log[0], $log[1]);
            }
            $this->logs = array();
            return true;
        }
        return false;
    }
    /**
     * Return iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->logs);
    }
    /**
     * @return int
     */
    public function count()
    {
        return count($this->logs);
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\AbstractObject as ResourceObject;
/**
 * Interface for resource log writer
 *
 * @package BEAR.Resource
 */
interface LogWriterInterface
{
    /**
     * Resource log write
     *
     * @param RequestInterface $request
     * @param AbstractObject   $result
     *
     * @return bool true if log written
     */
    public function write(RequestInterface $request, ResourceObject $result);
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\LogWriterInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\AbstractObject as ResourceObject;
/**
 * Writer collection
 */
final class Collection implements LogWriterInterface
{
    /**
     * \BEAR\Resource\LogWriterInterface[]
     *
     * @var array
     */
    private $writers = array();
    /**
     * @param array $writers
     *
     * @Inject
     * @Named("log_writers")
     */
    public function __construct(array $writers)
    {
        $this->writers = $writers;
    }
    /**
     * {@inheritdoc}
     */
    public function write(RequestInterface $request, ResourceObject $result)
    {
        foreach ($this->writers as $writer) {
            /** @var $writer \BEAR\Resource\LogWriterInterface */
            $writer->write($request, $result);
        }
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\RequestInterface;
use BEAR\Resource\LogWriterInterface;
use FirePHP;
use BEAR\Resource\AbstractObject as ResourceObject;
use Traversable;
use Ray\Di\Di\Inject;
/**
 * Fire logger
 */
final class Fire implements LogWriterInterface
{
    /**
     * @var \FirePHP
     */
    private $fire;
    /**
     * @param FirePHP $fire
     *
     * @Inject(optional = true)
     */
    public function __construct(FirePHP $fire = null)
    {
        $this->fire = $fire ?: FirePHP::getInstance(true);
    }
    /**
     * {@inheritdoc}
     */
    public function write(RequestInterface $request, ResourceObject $result)
    {
        if (headers_sent()) {
            return;
        }
        $requestLabel = $request->toUriWithMethod();
        $this->fire->group($requestLabel);
        $this->fireResourceObjectLog($result);
        $this->fire->groupEnd();
    }
    /**
     * Fire resource object log
     *
     * @param ResourceObject $result
     */
    private function fireResourceObjectLog(ResourceObject $result)
    {
        // code
        $this->fire->log($result->code, 'code');
        // headers
        $headers = array();
        $headers[] = array('name', 'value');
        foreach ($result->headers as $name => $value) {
            $headers[] = array($name, $value);
        }
        $this->fire->table('headers', $headers);
        // body
        $body = $this->normalize($result->body);
        $isTable = is_array($body) && isset($body[0]) && isset($body[1]) && array_keys($body[0]) === array_keys($body[1]);
        if ($isTable) {
            $table = array();
            $table[] = array_values(array_keys($body[0]));
            foreach ((array) $body as $val) {
                $table[] = array_values((array) $val);
            }
            $this->fire->table('body', $table);
        } else {
            $this->fire->log($body, 'body');
        }
        // links
        $links = array(array('rel', 'uri'));
        foreach ($result->links as $rel => $uri) {
            $links[] = array($rel, $uri);
        }
        if (count($links) > 1) {
            $this->fire->table('links', $links);
        }
        $this->fire->group('view', array('Collapsed' => true));
        $this->fire->log($result->view);
        $this->fire->groupEnd();
    }
    /**
     * Format log data
     *
     * @param  mixed $body
     *
     * @return mixed
     * @todo scan all prop like print_o, then eliminate all resource/PDO/etc.. unrealisable objects...not like this.
     */
    public function normalize(&$body)
    {
        if (!(is_array($body) || $body instanceof Traversable)) {
            return $body;
        }
        array_walk_recursive($body, function (&$value) {
            if ($value instanceof RequestInterface) {
                $value = '(Request) ' . $value->toUri();
            }
            if ($value instanceof ResourceObject) {
                /** @var $value ResourceObject */
                $value = '(ResourceObject) ' . get_class($value) . json_encode($value->body);
            }
            if ($value instanceof \PDO || $value instanceof \PDOStatement) {
                $value = '(PDO) ' . get_class($value);
            }
            if ($value instanceof \Doctrine\DBAL\Connection) {
                $value = '(\\Doctrine\\DBAL\\Connection) ' . get_class($value);
            }
            if (is_resource($value)) {
                $value = '(resource) ' . gettype($value);
            }
            if (is_object($value)) {
                $value = '(object) ' . get_class($value);
            }
        });
        return $body;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\RequestInterface;
use Ray\Di\ProviderInterface;
use BEAR\Resource\LogWriterInterface;
use BEAR\Resource\AbstractObject as ResourceObject;
/**
 * Zf2 logger
 */
final class Zf2Log implements LogWriterInterface
{
    /**
     * @var ProviderInterface
     */
    private $provider;
    /**
     * @var string
     */
    private $pageId;
    /**
     * @var \Zend\Log\LoggerInterface
     */
    private $logger;
    /**
     * @param \Ray\Di\ProviderInterface $provider
     *
     * @Inject
     */
    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
        $this->pageId = rtrim(base64_encode(pack('H*', uniqid())), '=');
    }
    /**
     * {@inheritdoc}
     */
    public function write(RequestInterface $request, ResourceObject $result)
    {
        $this->logger = $this->provider->get();
        $id = "{$this->pageId}";
        /** @var $logger \Zend\Log\LoggerInterface */
        $msg = "id:{$id}\treq:" . $request->toUriWithMethod();
        $msg .= '	code:' . $result->code;
        $msg .= '	body:' . json_encode($result->body);
        $msg .= '	header:' . json_encode($result->headers);
        if (isset($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
            $path .= $_GET ? '?' : '';
            $path .= http_build_query($_GET);
        } else {
            $path = '/';
        }
        $msg .= "\tpath:{$path}";
        $this->logger->info($msg, array('page' => $this->pageId));
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use Ray\Di\ProviderInterface;
use Zend\Log\Logger;
use BEAR\Sunday\Inject\LogDirInject;
use Zend\Db\Adapter\Adapter;
use Zend\Log\Writer\Db;
use Zend\Log\Writer\Syslog;
/**
 * Zf2 logger provider
 */
final class Zf2LogProvider implements ProviderInterface
{
    /**
     * @var Adapter
     */
    private $db;
    /**
     * @var \Zend\Log\Logger
     */
    private $zf2Log;
    /**
     * @param $logDir string
     */
    public function __construct($logDir)
    {
        $this->zf2Log = new Logger();
        $this->zf2Log->addWriter(new Syslog());
        $dbConfig = array('driver' => 'Pdo_Sqlite', 'dsn' => 'sqlite:' . $logDir . '/resource.db');
        $this->db = new Adapter($dbConfig);
    }
    /**
     * @return Logger
     */
    public function get()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS log(timestamp, message, priority, priorityName, extra_page)', Adapter::QUERY_MODE_EXECUTE);
        $writer = new Db($this->db, 'log');
        $this->zf2Log->addWriter($writer);
        return $this->zf2Log;
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log;

use Traversable;
/**
 * @category   Zend
 * @package    Zend_Log
 */
interface LoggerInterface
{
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function emerg($message, $extra = array());
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function alert($message, $extra = array());
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function crit($message, $extra = array());
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function err($message, $extra = array());
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function warn($message, $extra = array());
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function notice($message, $extra = array());
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function info($message, $extra = array());
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function debug($message, $extra = array());
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log;

use DateTime;
use ErrorException;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\SplPriorityQueue;
/**
 * Logging messages with a stack of backends
 *
 * @category   Zend
 * @package    Zend_Log
 */
class Logger implements LoggerInterface
{
    /**
     * @const int defined from the BSD Syslog message severities
     * @link http://tools.ietf.org/html/rfc3164
     */
    const EMERG = 0;
    const ALERT = 1;
    const CRIT = 2;
    const ERR = 3;
    const WARN = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;
    /**
     * Map of PHP error constants to log priorities
     *
     * @var array
     */
    protected static $errorPriorityMap = array(E_NOTICE => self::NOTICE, E_USER_NOTICE => self::NOTICE, E_WARNING => self::WARN, E_CORE_WARNING => self::WARN, E_USER_WARNING => self::WARN, E_ERROR => self::ERR, E_USER_ERROR => self::ERR, E_CORE_ERROR => self::ERR, E_RECOVERABLE_ERROR => self::ERR, E_STRICT => self::DEBUG, E_DEPRECATED => self::DEBUG, E_USER_DEPRECATED => self::DEBUG);
    /**
     * List of priority code => priority (short) name
     *
     * @var array
     */
    protected $priorities = array(self::EMERG => 'EMERG', self::ALERT => 'ALERT', self::CRIT => 'CRIT', self::ERR => 'ERR', self::WARN => 'WARN', self::NOTICE => 'NOTICE', self::INFO => 'INFO', self::DEBUG => 'DEBUG');
    /**
     * Writers
     *
     * @var SplPriorityQueue
     */
    protected $writers;
    /**
     * Writer plugins
     *
     * @var WriterPluginManager
     */
    protected $writerPlugins;
    /**
     * Registered error handler
     *
     * @var bool
     */
    protected static $registeredErrorHandler = false;
    /**
     * Registered exception handler
     *
     * @var bool
     */
    protected static $registeredExceptionHandler = false;
    /**
     * Constructor
     *
     * @todo support configuration (writers, dateTimeFormat, and writer plugin manager)
     * @return Logger
     */
    public function __construct()
    {
        $this->writers = new SplPriorityQueue();
    }
    /**
     * Shutdown all writers
     *
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->writers as $writer) {
            try {
                $writer->shutdown();
            } catch (\Exception $e) {
                
            }
        }
    }
    /**
     * Get writer plugin manager
     *
     * @return WriterPluginManager
     */
    public function getWriterPluginManager()
    {
        if (null === $this->writerPlugins) {
            $this->setWriterPluginManager(new WriterPluginManager());
        }
        return $this->writerPlugins;
    }
    /**
     * Set writer plugin manager
     *
     * @param  string|WriterPluginManager $plugins
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function setWriterPluginManager($plugins)
    {
        if (is_string($plugins)) {
            $plugins = new $plugins();
        }
        if (!$plugins instanceof WriterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf('Writer plugin manager must extend %s\\WriterPluginManager; received %s', __NAMESPACE__, is_object($plugins) ? get_class($plugins) : gettype($plugins)));
        }
        $this->writerPlugins = $plugins;
        return $this;
    }
    /**
     * Get writer instance
     *
     * @param string $name
     * @param array|null $options
     * @return Writer\WriterInterface
     */
    public function writerPlugin($name, array $options = null)
    {
        return $this->getWriterPluginManager()->get($name, $options);
    }
    /**
     * Add a writer to a logger
     *
     * @param  string|Writer\WriterInterface $writer
     * @param  int $priority
     * @param  array|null $options
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function addWriter($writer, $priority = 1, array $options = null)
    {
        if (is_string($writer)) {
            $writer = $this->writerPlugin($writer, $options);
        } elseif (!$writer instanceof Writer\WriterInterface) {
            throw new Exception\InvalidArgumentException(sprintf('Writer must implement Zend\\Log\\Writer; received "%s"', is_object($writer) ? get_class($writer) : gettype($writer)));
        }
        $this->writers->insert($writer, $priority);
        return $this;
    }
    /**
     * Get writers
     *
     * @return SplPriorityQueue
     */
    public function getWriters()
    {
        return $this->writers;
    }
    /**
     * Set the writers
     *
     * @param  SplPriorityQueue $writers
     * @return Logger
     * @throws Exception\InvalidArgumentException
     */
    public function setWriters(SplPriorityQueue $writers)
    {
        foreach ($writers->toArray() as $writer) {
            if (!$writer instanceof Writer\WriterInterface) {
                throw new Exception\InvalidArgumentException('Writers must be a SplPriorityQueue of Zend\\Log\\Writer');
            }
        }
        $this->writers = $writers;
        return $this;
    }
    /**
     * Add a message as a log entry
     *
     * @param  int $priority
     * @param  mixed $message
     * @param  array|Traversable $extra
     * @return Logger
     * @throws Exception\InvalidArgumentException if message can't be cast to string
     * @throws Exception\InvalidArgumentException if extra can't be iterated over
     * @throws Exception\RuntimeException if no log writer specified
     */
    public function log($priority, $message, $extra = array())
    {
        if (!is_int($priority) || $priority < 0 || $priority >= count($this->priorities)) {
            throw new Exception\InvalidArgumentException(sprintf('$priority must be an integer > 0 and < %d; received %s', count($this->priorities), var_export($priority, 1)));
        }
        if (is_object($message) && !method_exists($message, '__toString')) {
            throw new Exception\InvalidArgumentException('$message must implement magic __toString() method');
        }
        if (!is_array($extra) && !$extra instanceof Traversable) {
            throw new Exception\InvalidArgumentException('$extra must be an array or implement Traversable');
        } elseif ($extra instanceof Traversable) {
            $extra = ArrayUtils::iteratorToArray($extra);
        }
        if ($this->writers->count() === 0) {
            throw new Exception\RuntimeException('No log writer specified');
        }
        $timestamp = new DateTime();
        if (is_array($message)) {
            $message = var_export($message, true);
        }
        foreach ($this->writers->toArray() as $writer) {
            $writer->write(array('timestamp' => $timestamp, 'priority' => (int) $priority, 'priorityName' => $this->priorities[$priority], 'message' => (string) $message, 'extra' => $extra));
        }
        return $this;
    }
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function emerg($message, $extra = array())
    {
        return $this->log(self::EMERG, $message, $extra);
    }
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function alert($message, $extra = array())
    {
        return $this->log(self::ALERT, $message, $extra);
    }
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function crit($message, $extra = array())
    {
        return $this->log(self::CRIT, $message, $extra);
    }
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function err($message, $extra = array())
    {
        return $this->log(self::ERR, $message, $extra);
    }
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function warn($message, $extra = array())
    {
        return $this->log(self::WARN, $message, $extra);
    }
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function notice($message, $extra = array())
    {
        return $this->log(self::NOTICE, $message, $extra);
    }
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function info($message, $extra = array())
    {
        return $this->log(self::INFO, $message, $extra);
    }
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return Logger
     */
    public function debug($message, $extra = array())
    {
        return $this->log(self::DEBUG, $message, $extra);
    }
    /**
     * Register logging system as an error handler to log PHP errors
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php
     * @param  Logger $logger
     * @return bool
     * @throws Exception\InvalidArgumentException if logger is null
     */
    public static function registerErrorHandler(Logger $logger)
    {
        // Only register once per instance
        if (static::$registeredErrorHandler) {
            return false;
        }
        if ($logger === null) {
            throw new Exception\InvalidArgumentException('Invalid Logger specified');
        }
        $errorHandlerMap = static::$errorPriorityMap;
        set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) use($errorHandlerMap, $logger) {
            $errorLevel = error_reporting();
            if ($errorLevel & $errno) {
                if (isset($errorHandlerMap[$errno])) {
                    $priority = $errorHandlerMap[$errno];
                } else {
                    $priority = Logger::INFO;
                }
                $logger->log($priority, $errstr, array('errno' => $errno, 'file' => $errfile, 'line' => $errline, 'context' => $errcontext));
            }
        });
        static::$registeredErrorHandler = true;
        return true;
    }
    /**
     * Unregister error handler
     *
     */
    public static function unregisterErrorHandler()
    {
        restore_error_handler();
        static::$registeredErrorHandler = false;
    }
    /**
     * Register logging system as an exception handler to log PHP exceptions
     *
     * @link http://www.php.net/manual/en/function.set-exception-handler.php
     * @param Logger $logger
     * @return bool
     * @throws Exception\InvalidArgumentException if logger is null
     */
    public static function registerExceptionHandler(Logger $logger)
    {
        // Only register once per instance
        if (static::$registeredExceptionHandler) {
            return false;
        }
        if ($logger === null) {
            throw new Exception\InvalidArgumentException('Invalid Logger specified');
        }
        $errorPriorityMap = static::$errorPriorityMap;
        set_exception_handler(function ($exception) use($logger, $errorPriorityMap) {
            $logMessages = array();
            do {
                $priority = Logger::ERR;
                if ($exception instanceof ErrorException && isset($errorPriorityMap[$exception->getSeverity()])) {
                    $priority = $errorPriorityMap[$exception->getSeverity()];
                }
                $extra = array('file' => $exception->getFile(), 'line' => $exception->getLine(), 'trace' => $exception->getTrace());
                if (isset($exception->xdebug_message)) {
                    $extra['xdebug'] = $exception->xdebug_message;
                }
                $logMessages[] = array('priority' => $priority, 'message' => $exception->getMessage(), 'extra' => $extra);
                $exception = $exception->getPrevious();
            } while ($exception);
            foreach (array_reverse($logMessages) as $logMessage) {
                $logger->log($logMessage['priority'], $logMessage['message'], $logMessage['extra']);
            }
        });
        static::$registeredExceptionHandler = true;
        return true;
    }
    /**
     * Unregister exception handler
     */
    public static function unregisterExceptionHandler()
    {
        restore_exception_handler();
        static::$registeredExceptionHandler = false;
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Stdlib
 */
namespace Zend\Stdlib;

use Serializable;
/**
 * Serializable version of SplPriorityQueue
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 *
 * @category   Zend
 * @package    Zend_Stdlib
 */
class SplPriorityQueue extends \SplPriorityQueue implements Serializable
{
    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    protected $serial = PHP_INT_MAX;
    /**
     * Insert a value with a given priority
     *
     * Utilizes {@var $serial} to ensure that values of equal priority are
     * emitted in the same order in which they are inserted.
     *
     * @param  mixed $datum
     * @param  mixed $priority
     * @return void
     */
    public function insert($datum, $priority)
    {
        if (!is_array($priority)) {
            $priority = array($priority, $this->serial--);
        }
        parent::insert($datum, $priority);
    }
    /**
     * Serialize to an array
     *
     * Array will be priority => data pairs
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach (clone $this as $item) {
            $array[] = $item;
        }
        return $array;
    }
    /**
     * Serialize
     *
     * @return string
     */
    public function serialize()
    {
        $clone = clone $this;
        $clone->setExtractFlags(self::EXTR_BOTH);
        $data = array();
        foreach ($clone as $item) {
            $data[] = $item;
        }
        return serialize($data);
    }
    /**
     * Deserialize
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        foreach (unserialize($data) as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log\Writer;

use Zend\Log\Filter\FilterInterface as Filter;
use Zend\Log\Formatter\FormatterInterface as Formatter;
/**
 * @category   Zend
 * @package    Zend_Log
 */
interface WriterInterface
{
    /**
     * Add a log filter to the writer
     *
     * @param  int|Filter $filter
     * @return WriterInterface
     */
    public function addFilter($filter);
    /**
     * Set a message formatter for the writer
     *
     * @param Formatter $formatter
     * @return WriterInterface
     */
    public function setFormatter(Formatter $formatter);
    /**
     * Write a log message
     *
     * @param  array $event
     * @return WriterInterface
     */
    public function write(array $event);
    /**
     * Perform shutdown activities
     *
     * @return void
     */
    public function shutdown();
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log\Writer;

use Zend\Log\Exception;
use Zend\Log\Filter;
use Zend\Log\Formatter\FormatterInterface as Formatter;
use Zend\Stdlib\ErrorHandler;
/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Writer
 */
abstract class AbstractWriter implements WriterInterface
{
    /**
     * Filter plugins
     *
     * @var FilterPluginManager
     */
    protected $filterPlugins;
    /**
     * Filter chain
     *
     * @var Filter\FilterInterface[]
     */
    protected $filters = array();
    /**
     * Formats the log message before writing
     *
     * @var Formatter
     */
    protected $formatter;
    /**
     * Use Zend\Stdlib\ErrorHandler to report errors during calls to write
     *
     * @var bool
     */
    protected $convertWriteErrorsToExceptions = true;
    /**
     * Error level passed to Zend\Stdlib\ErrorHandler::start for errors reported during calls to write
     *
     * @var bool
     */
    protected $errorsToExceptionsConversionLevel = E_WARNING;
    /**
     * Add a filter specific to this writer.
     *
     * @param  int|string|Filter\FilterInterface $filter
     * @param  array|null $options
     * @return AbstractWriter
     * @throws Exception\InvalidArgumentException
     */
    public function addFilter($filter, array $options = null)
    {
        if (is_int($filter)) {
            $filter = new Filter\Priority($filter);
        }
        if (is_string($filter)) {
            $filter = $this->filterPlugin($filter, $options);
        }
        if (!$filter instanceof Filter\FilterInterface) {
            throw new Exception\InvalidArgumentException(sprintf('Writer must implement Zend\\Log\\Filter\\FilterInterface; received "%s"', is_object($filter) ? get_class($filter) : gettype($filter)));
        }
        $this->filters[] = $filter;
        return $this;
    }
    /**
     * Get filter plugin manager
     *
     * @return FilterPluginManager
     */
    public function getFilterPluginManager()
    {
        if (null === $this->filterPlugins) {
            $this->setFilterPluginManager(new FilterPluginManager());
        }
        return $this->filterPlugins;
    }
    /**
     * Set filter plugin manager
     *
     * @param  string|FilterPluginManager $plugins
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFilterPluginManager($plugins)
    {
        if (is_string($plugins)) {
            $plugins = new $plugins();
        }
        if (!$plugins instanceof FilterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf('Writer plugin manager must extend %s\\FilterPluginManager; received %s', __NAMESPACE__, is_object($plugins) ? get_class($plugins) : gettype($plugins)));
        }
        $this->filterPlugins = $plugins;
        return $this;
    }
    /**
     * Get filter instance
     *
     * @param string $name
     * @param array|null $options
     * @return Filter\FilterInterface
     */
    public function filterPlugin($name, array $options = null)
    {
        return $this->getFilterPluginManager()->get($name, $options);
    }
    /**
     * Log a message to this writer.
     *
     * @param array $event log data event
     * @return void
     */
    public function write(array $event)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filter($event)) {
                return;
            }
        }
        $errorHandlerStarted = false;
        if ($this->convertWriteErrorsToExceptions && !ErrorHandler::started()) {
            ErrorHandler::start($this->errorsToExceptionsConversionLevel);
            $errorHandlerStarted = true;
        }
        try {
            $this->doWrite($event);
        } catch (\Exception $e) {
            if ($errorHandlerStarted) {
                ErrorHandler::stop();
                $errorHandlerStarted = false;
            }
            throw $e;
        }
        if ($errorHandlerStarted) {
            $error = ErrorHandler::stop();
            $errorHandlerStarted = false;
            if ($error) {
                throw new Exception\RuntimeException('Unable to write', 0, $error);
            }
        }
    }
    /**
     * Set a new formatter for this writer
     *
     * @param  Formatter $formatter
     * @return self
     */
    public function setFormatter(Formatter $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }
    /**
     * Set convert write errors to exception flag
     *
     * @param bool $ignoreWriteErrors
     */
    public function setConvertWriteErrorsToExceptions($convertErrors)
    {
        $this->convertWriteErrorsToExceptions = $convertErrors;
    }
    /**
     * Perform shutdown activities such as closing open resources
     *
     * @return void
     */
    public function shutdown()
    {
        
    }
    /**
     * Write a message to the log
     *
     * @param array $event log data event
     * @return void
     */
    protected abstract function doWrite(array $event);
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log\Writer;

use Zend\Log\Exception;
use Zend\Log\Logger;
use Zend\Log\Formatter\Simple as SimpleFormatter;
/**
 * Writes log messages to syslog
 *
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Writer
 */
class Syslog extends AbstractWriter
{
    /**
     * Maps Zend_Log priorities to PHP's syslog priorities
     *
     * @var array
     */
    protected $priorities = array(Logger::EMERG => LOG_EMERG, Logger::ALERT => LOG_ALERT, Logger::CRIT => LOG_CRIT, Logger::ERR => LOG_ERR, Logger::WARN => LOG_WARNING, Logger::NOTICE => LOG_NOTICE, Logger::INFO => LOG_INFO, Logger::DEBUG => LOG_DEBUG);
    /**
     * The default log priority - for unmapped custom priorities
     *
     * @var string
     */
    protected $defaultPriority = LOG_NOTICE;
    /**
     * Last application name set by a syslog-writer instance
     *
     * @var string
     */
    protected static $lastApplication;
    /**
     * Last facility name set by a syslog-writer instance
     *
     * @var string
     */
    protected static $lastFacility;
    /**
     * Application name used by this syslog-writer instance
     *
     * @var string
     */
    protected $appName = 'Zend\\Log';
    /**
     * Facility used by this syslog-writer instance
     *
     * @var int
     */
    protected $facility = LOG_USER;
    /**
     * Types of program available to logging of message
     *
     * @var array
     */
    protected $validFacilities = array();
    /**
     * Constructor
     *
     * @param  array $params Array of options; may include "application" and "facility" keys
     * @return Syslog
     */
    public function __construct(array $params = array())
    {
        if (isset($params['application'])) {
            $this->appName = $params['application'];
        }
        $runInitializeSyslog = true;
        if (isset($params['facility'])) {
            $this->setFacility($params['facility']);
            $runInitializeSyslog = false;
        }
        if ($runInitializeSyslog) {
            $this->initializeSyslog();
        }
        $this->setFormatter(new SimpleFormatter('%message%'));
    }
    /**
     * Initialize values facilities
     *
     * @return void
     */
    protected function initializeValidFacilities()
    {
        $constants = array('LOG_AUTH', 'LOG_AUTHPRIV', 'LOG_CRON', 'LOG_DAEMON', 'LOG_KERN', 'LOG_LOCAL0', 'LOG_LOCAL1', 'LOG_LOCAL2', 'LOG_LOCAL3', 'LOG_LOCAL4', 'LOG_LOCAL5', 'LOG_LOCAL6', 'LOG_LOCAL7', 'LOG_LPR', 'LOG_MAIL', 'LOG_NEWS', 'LOG_SYSLOG', 'LOG_USER', 'LOG_UUCP');
        foreach ($constants as $constant) {
            if (defined($constant)) {
                $this->validFacilities[] = constant($constant);
            }
        }
    }
    /**
     * Initialize syslog / set application name and facility
     *
     * @return void
     */
    protected function initializeSyslog()
    {
        static::$lastApplication = $this->appName;
        static::$lastFacility = $this->facility;
        openlog($this->appName, LOG_PID, $this->facility);
    }
    /**
     * Set syslog facility
     *
     * @param int $facility Syslog facility
     * @return Syslog
     * @throws Exception\InvalidArgumentException for invalid log facility
     */
    public function setFacility($facility)
    {
        if ($this->facility === $facility) {
            return $this;
        }
        if (!count($this->validFacilities)) {
            $this->initializeValidFacilities();
        }
        if (!in_array($facility, $this->validFacilities)) {
            throw new Exception\InvalidArgumentException('Invalid log facility provided; please see http://php.net/openlog for a list of valid facility values');
        }
        if ('WIN' == strtoupper(substr(PHP_OS, 0, 3)) && $facility !== LOG_USER) {
            throw new Exception\InvalidArgumentException('Only LOG_USER is a valid log facility on Windows');
        }
        $this->facility = $facility;
        $this->initializeSyslog();
        return $this;
    }
    /**
     * Set application name
     *
     * @param string $appName Application name
     * @return Syslog
     */
    public function setApplicationName($appName)
    {
        if ($this->appName === $appName) {
            return $this;
        }
        $this->appName = $appName;
        $this->initializeSyslog();
        return $this;
    }
    /**
     * Close syslog.
     *
     * @return void
     */
    public function shutdown()
    {
        closelog();
    }
    /**
     * Write a message to syslog.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        if (array_key_exists($event['priority'], $this->priorities)) {
            $priority = $this->priorities[$event['priority']];
        } else {
            $priority = $this->defaultPriority;
        }
        if ($this->appName !== static::$lastApplication || $this->facility !== static::$lastFacility) {
            $this->initializeSyslog();
        }
        $message = $this->formatter->format($event);
        syslog($priority, $message);
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log\Formatter;

/**
 * @category   Zend
 * @package    Zend_Log
 */
interface FormatterInterface
{
    /**
     * Default format specifier for DateTime objects is ISO 8601
     *
     * @see http://php.net/manual/en/function.date.php
     */
    const DEFAULT_DATETIME_FORMAT = 'c';
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param array $event event data
     * @return string formatted line to write to the log
     */
    public function format($event);
    /**
     * Get the format specifier for DateTime objects
     *
     * @return string
     */
    public function getDateTimeFormat();
    /**
     * Set the format specifier for DateTime objects
     *
     * @see http://php.net/manual/en/function.date.php
     * @param string $dateTimeFormat DateTime format
     * @return FormatterInterface
     */
    public function setDateTimeFormat($dateTimeFormat);
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log\Formatter;

use DateTime;
use Traversable;
/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Formatter
 */
class Base implements FormatterInterface
{
    /**
     * Format specifier for DateTime objects in event data (default: ISO 8601)
     *
     * @see http://php.net/manual/en/function.date.php
     * @var string
     */
    protected $dateTimeFormat = self::DEFAULT_DATETIME_FORMAT;
    /**
     * Class constructor
     *
     * @see http://php.net/manual/en/function.date.php
     * @param null|string $dateTimeFormat Format for DateTime objects
     */
    public function __construct($dateTimeFormat = null)
    {
        if (null !== $dateTimeFormat) {
            $this->dateTimeFormat = $dateTimeFormat;
        }
    }
    /**
     * Formats data to be written by the writer.
     *
     * @param array $event event data
     * @return array
     */
    public function format($event)
    {
        foreach ($event as $key => $value) {
            // Keep extra as an array
            if ('extra' === $key) {
                $event[$key] = self::format($value);
            } else {
                $event[$key] = $this->normalize($value);
            }
        }
        return $event;
    }
    /**
     * Normalize all non-scalar data types (except null) in a string value
     *
     * @param mixed $value
     * @return mixed
     */
    protected function normalize($value)
    {
        if (is_scalar($value) || null === $value) {
            return $value;
        }
        if ($value instanceof DateTime) {
            $value = $value->format($this->getDateTimeFormat());
        } elseif (is_array($value) || $value instanceof Traversable) {
            if ($value instanceof Traversable) {
                $value = iterator_to_array($value);
            }
            foreach ($value as $key => $subvalue) {
                $value[$key] = $this->normalize($subvalue);
            }
            $value = json_encode($value);
        } elseif (is_object($value) && !method_exists($value, '__toString')) {
            $value = sprintf('object(%s) %s', get_class($value), json_encode($value));
        } elseif (is_resource($value)) {
            $value = sprintf('resource(%s)', get_resource_type($value));
        } elseif (!is_object($value)) {
            $value = gettype($value);
        }
        return (string) $value;
    }
    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }
    /**
     * {@inheritDoc}
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;
        return $this;
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log\Formatter;

use Zend\Log\Exception;
/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Formatter
 */
class Simple extends Base
{
    const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%): %message% %extra%';
    /**
     * Format specifier for log messages
     *
     * @var string
     */
    protected $format;
    /**
     * Class constructor
     *
     * @see http://php.net/manual/en/function.date.php
     * @param null|string $format Format specifier for log messages
     * @param null|string $dateTimeFormat Format specifier for DateTime objects in event data
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($format = null, $dateTimeFormat = null)
    {
        if (isset($format) && !is_string($format)) {
            throw new Exception\InvalidArgumentException('Format must be a string');
        }
        $this->format = isset($format) ? $format : static::DEFAULT_FORMAT;
        parent::__construct($dateTimeFormat);
    }
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param array $event event data
     * @return string formatted line to write to the log
     */
    public function format($event)
    {
        $output = $this->format;
        $event = parent::format($event);
        foreach ($event as $name => $value) {
            if ('extra' == $name && count($value)) {
                $value = $this->normalize($value);
            } elseif ('extra' == $name) {
                // Don't print an empty array
                $value = '';
            }
            $output = str_replace("%{$name}%", $value, $output);
        }
        if (isset($event['extra']) && empty($event['extra']) && false !== strpos($this->format, '%extra%')) {
            $output = rtrim($output, ' ');
        }
        return $output;
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter;

/**
 *
 * @property Driver\DriverInterface $driver
 * @property Platform\PlatformInterface $platform
 */
interface AdapterInterface
{
    /**
     * @return Driver\DriverInterface
     */
    public function getDriver();
    /**
     * @return Platform\PlatformInterface
     */
    public function getPlatform();
}
namespace Zend\Db\Adapter\Profiler;

interface ProfilerAwareInterface
{
    public function setProfiler(ProfilerInterface $profiler);
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter;

use Zend\Db\ResultSet;
/**
 * @property Driver\DriverInterface $driver
 * @property Platform\PlatformInterface $platform
 */
class Adapter implements AdapterInterface, Profiler\ProfilerAwareInterface
{
    /**
     * Query Mode Constants
     */
    const QUERY_MODE_EXECUTE = 'execute';
    const QUERY_MODE_PREPARE = 'prepare';
    /**
     * Prepare Type Constants
     */
    const PREPARE_TYPE_POSITIONAL = 'positional';
    const PREPARE_TYPE_NAMED = 'named';
    const FUNCTION_FORMAT_PARAMETER_NAME = 'formatParameterName';
    const FUNCTION_QUOTE_IDENTIFIER = 'quoteIdentifier';
    const FUNCTION_QUOTE_VALUE = 'quoteValue';
    const VALUE_QUOTE_SEPARATOR = 'quoteSeparator';
    /**
     * @var Driver\DriverInterface
     */
    protected $driver = null;
    /**
     * @var Platform\PlatformInterface
     */
    protected $platform = null;
    /**
     * @var Profiler\ProfilerInterface
     */
    protected $profiler = null;
    /**
     * @var ResultSet\ResultSetInterface
     */
    protected $queryResultSetPrototype = null;
    /**
     * @var Driver\StatementInterface
     */
    protected $lastPreparedStatement = null;
    /**
     * @param Driver\DriverInterface|array $driver
     * @param Platform\PlatformInterface $platform
     * @param ResultSet\ResultSetInterface $queryResultPrototype
     * @param Profiler\ProfilerInterface $profiler
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($driver, Platform\PlatformInterface $platform = null, ResultSet\ResultSetInterface $queryResultPrototype = null, Profiler\ProfilerInterface $profiler = null)
    {
        // first argument can be an array of parameters
        $parameters = array();
        if (is_array($driver)) {
            $parameters = $driver;
            if ($profiler === null && isset($parameters['profiler'])) {
                $profiler = $this->createProfiler($parameters);
            }
            $driver = $this->createDriver($parameters);
        } elseif (!$driver instanceof Driver\DriverInterface) {
            throw new Exception\InvalidArgumentException('The supplied or instantiated driver object does not implement Zend\\Db\\Adapter\\Driver\\DriverInterface');
        }
        $driver->checkEnvironment();
        $this->driver = $driver;
        if ($platform == null) {
            $platform = $this->createPlatform($parameters);
        }
        $this->platform = $platform;
        $this->queryResultSetPrototype = $queryResultPrototype ?: new ResultSet\ResultSet();
        if ($profiler) {
            $this->setProfiler($profiler);
        }
    }
    /**
     * @param Profiler\ProfilerInterface $profiler
     * @return Adapter
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        if ($this->driver instanceof Profiler\ProfilerAwareInterface) {
            $this->driver->setProfiler($profiler);
        }
        return $this;
    }
    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }
    /**
     * getDriver()
     *
     * @throws Exception\RuntimeException
     * @return Driver\DriverInterface
     */
    public function getDriver()
    {
        if ($this->driver == null) {
            throw new Exception\RuntimeException('Driver has not been set or configured for this adapter.');
        }
        return $this->driver;
    }
    /**
     * @return Platform\PlatformInterface
     */
    public function getPlatform()
    {
        return $this->platform;
    }
    /**
     * @return ResultSet\ResultSetInterface
     */
    public function getQueryResultSetPrototype()
    {
        return $this->queryResultSetPrototype;
    }
    public function getCurrentSchema()
    {
        return $this->driver->getConnection()->getCurrentSchema();
    }
    /**
     * query() is a convenience function
     *
     * @param string $sql
     * @param string|array|ParameterContainer $parametersOrQueryMode
     * @throws Exception\InvalidArgumentException
     * @return Driver\StatementInterface|ResultSet\ResultSet
     */
    public function query($sql, $parametersOrQueryMode = self::QUERY_MODE_PREPARE)
    {
        if (is_string($parametersOrQueryMode) && in_array($parametersOrQueryMode, array(self::QUERY_MODE_PREPARE, self::QUERY_MODE_EXECUTE))) {
            $mode = $parametersOrQueryMode;
            $parameters = null;
        } elseif (is_array($parametersOrQueryMode) || $parametersOrQueryMode instanceof ParameterContainer) {
            $mode = self::QUERY_MODE_PREPARE;
            $parameters = $parametersOrQueryMode;
        } else {
            throw new Exception\InvalidArgumentException('Parameter 2 to this method must be a flag, an array, or ParameterContainer');
        }
        if ($mode == self::QUERY_MODE_PREPARE) {
            $this->lastPreparedStatement = null;
            $this->lastPreparedStatement = $this->driver->createStatement($sql);
            $this->lastPreparedStatement->prepare();
            if (is_array($parameters) || $parameters instanceof ParameterContainer) {
                $this->lastPreparedStatement->setParameterContainer(is_array($parameters) ? new ParameterContainer($parameters) : $parameters);
                $result = $this->lastPreparedStatement->execute();
            } else {
                return $this->lastPreparedStatement;
            }
        } else {
            $result = $this->driver->getConnection()->execute($sql);
        }
        if ($result instanceof Driver\ResultInterface && $result->isQueryResult()) {
            $resultSet = clone $this->queryResultSetPrototype;
            $resultSet->initialize($result);
            return $resultSet;
        }
        return $result;
    }
    /**
     * Create statement
     *
     * @param  string $initialSql
     * @param  ParameterContainer $initialParameters
     * @return Driver\StatementInterface
     */
    public function createStatement($initialSql = null, $initialParameters = null)
    {
        $statement = $this->driver->createStatement($initialSql);
        if ($initialParameters == null || !$initialParameters instanceof ParameterContainer && is_array($initialParameters)) {
            $initialParameters = new ParameterContainer(is_array($initialParameters) ? $initialParameters : array());
        }
        $statement->setParameterContainer($initialParameters);
        return $statement;
    }
    public function getHelpers()
    {
        $functions = array();
        $platform = $this->platform;
        foreach (func_get_args() as $arg) {
            switch ($arg) {
                case self::FUNCTION_QUOTE_IDENTIFIER:
                    $functions[] = function ($value) use($platform) {
                        return $platform->quoteIdentifier($value);
                    };
                    break;
                case self::FUNCTION_QUOTE_VALUE:
                    $functions[] = function ($value) use($platform) {
                        return $platform->quoteValue($value);
                    };
                    break;
            }
        }
    }
    /**
     * @param $name
     * @throws Exception\InvalidArgumentException
     * @return Driver\DriverInterface|Platform\PlatformInterface
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'driver':
                return $this->driver;
            case 'platform':
                return $this->platform;
            default:
                throw new Exception\InvalidArgumentException('Invalid magic property on adapter');
        }
    }
    /**
     * @param array $parameters
     * @return Driver\DriverInterface
     * @throws \InvalidArgumentException
     * @throws Exception\InvalidArgumentException
     */
    protected function createDriver($parameters)
    {
        if (!isset($parameters['driver'])) {
            throw new Exception\InvalidArgumentException(__FUNCTION__ . ' expects a "driver" key to be present inside the parameters');
        }
        if ($parameters['driver'] instanceof Driver\DriverInterface) {
            return $parameters['driver'];
        }
        if (!is_string($parameters['driver'])) {
            throw new Exception\InvalidArgumentException(__FUNCTION__ . ' expects a "driver" to be a string or instance of DriverInterface');
        }
        $options = array();
        if (isset($parameters['options'])) {
            $options = (array) $parameters['options'];
            unset($parameters['options']);
        }
        $driverName = strtolower($parameters['driver']);
        switch ($driverName) {
            case 'mysqli':
                $driver = new Driver\Mysqli\Mysqli($parameters, null, null, $options);
                break;
            case 'sqlsrv':
                $driver = new Driver\Sqlsrv\Sqlsrv($parameters);
                break;
            case 'oci8':
                $driver = new Driver\Oci8\Oci8($parameters);
                break;
            case 'pgsql':
                $driver = new Driver\Pgsql\Pgsql($parameters);
                break;
            case 'ibmdb2':
                $driver = new Driver\IbmDb2\IbmDb2($parameters);
                break;
            case 'pdo':
            default:
                if ($driverName == 'pdo' || strpos($driverName, 'pdo') === 0) {
                    $driver = new Driver\Pdo\Pdo($parameters);
                }
        }
        if (!isset($driver) || !$driver instanceof Driver\DriverInterface) {
            throw new Exception\InvalidArgumentException('DriverInterface expected', null, null);
        }
        return $driver;
    }
    /**
     * @param Driver\DriverInterface $driver
     * @return Platform\PlatformInterface
     */
    protected function createPlatform($parameters)
    {
        if (isset($parameters['platform'])) {
            $platformName = $parameters['platform'];
        } elseif ($this->driver instanceof Driver\DriverInterface) {
            $platformName = $this->driver->getDatabasePlatformName(Driver\DriverInterface::NAME_FORMAT_CAMELCASE);
        } else {
            throw new Exception\InvalidArgumentException('A platform could not be determined from the provided configuration');
        }
        $options = isset($parameters['platform_options']) ? $parameters['platform_options'] : array();
        switch ($platformName) {
            case 'Mysql':
                return new Platform\Mysql($options);
            case 'SqlServer':
                return new Platform\SqlServer($options);
            case 'Oracle':
                return new Platform\Oracle($options);
            case 'Sqlite':
                return new Platform\Sqlite($options);
            case 'Postgresql':
                return new Platform\Postgresql($options);
            case 'IbmDb2':
                return new Platform\IbmDb2($options);
            default:
                return new Platform\Sql92($options);
        }
    }
    protected function createProfiler($parameters)
    {
        if ($parameters['profiler'] instanceof Profiler\ProfilerInterface) {
            $profiler = $parameters['profiler'];
        } elseif (is_bool($parameters['profiler'])) {
            $profiler = $parameters['profiler'] == true ? new Profiler\Profiler() : null;
        } else {
            throw new Exception\InvalidArgumentException('"profiler" parameter must be an instance of ProfilerInterface or a boolean');
        }
        return $profiler;
    }
    /**
     * @param array $parameters
     * @return Driver\DriverInterface
     * @throws \InvalidArgumentException
     * @throws Exception\InvalidArgumentException
     * @deprecated
     */
    protected function createDriverFromParameters(array $parameters)
    {
        return $this->createDriver($parameters);
    }
    /**
     * @param Driver\DriverInterface $driver
     * @return Platform\PlatformInterface
     * @deprecated
     */
    protected function createPlatformFromDriver(Driver\DriverInterface $driver)
    {
        return $this->createPlatform($driver);
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver;

interface DriverInterface
{
    const PARAMETERIZATION_POSITIONAL = 'positional';
    const PARAMETERIZATION_NAMED = 'named';
    const NAME_FORMAT_CAMELCASE = 'camelCase';
    const NAME_FORMAT_NATURAL = 'natural';
    /**
     * Get database platform name
     *
     * @param string $nameFormat
     * @return string
     */
    public function getDatabasePlatformName($nameFormat = self::NAME_FORMAT_CAMELCASE);
    /**
     * Check environment
     *
     * @return bool
     */
    public function checkEnvironment();
    /**
     * Get connection
     *
     * @return ConnectionInterface
     */
    public function getConnection();
    /**
     * Create statement
     *
     * @param string|resource $sqlOrResource
     * @return StatementInterface
     */
    public function createStatement($sqlOrResource = null);
    /**
     * Create result
     *
     * @param resource $resource
     * @return ResultInterface
     */
    public function createResult($resource);
    /**
     * Get prepare type
     *
     * @return array
     */
    public function getPrepareType();
    /**
     * Format parameter name
     *
     * @param string $name
     * @param mixed  $type
     * @return string
     */
    public function formatParameterName($name, $type = null);
    /**
     * Get last generated value
     *
     * @return mixed
     */
    public function getLastGeneratedValue();
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver\Feature;

interface DriverFeatureInterface
{
    /**
     * Setup the default features for Pdo
     *
     * @return DriverFeatureInterface
     */
    public function setupDefaultFeatures();
    /**
     * Add feature
     *
     * @param string $name
     * @param mixed $feature
     * @return DriverFeatureInterface
     */
    public function addFeature($name, $feature);
    /**
     * Get feature
     *
     * @param $name
     * @return mixed|false
     */
    public function getFeature($name);
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver\Pdo;

use PDOStatement;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\Feature\AbstractFeature;
use Zend\Db\Adapter\Driver\Feature\DriverFeatureInterface;
use Zend\Db\Adapter\Exception;
use Zend\Db\Adapter\Profiler;
class Pdo implements DriverInterface, DriverFeatureInterface, Profiler\ProfilerAwareInterface
{
    /**
     * @const
     */
    const FEATURES_DEFAULT = 'default';
    /**
     * @var Connection
     */
    protected $connection = null;
    /**
     * @var Statement
     */
    protected $statementPrototype = null;
    /**
     * @var Result
     */
    protected $resultPrototype = null;
    /**
     * @var array
     */
    protected $features = array();
    /**
     * @param array|Connection|\PDO $connection
     * @param null|Statement $statementPrototype
     * @param null|Result $resultPrototype
     * @param string $features
     */
    public function __construct($connection, Statement $statementPrototype = null, Result $resultPrototype = null, $features = self::FEATURES_DEFAULT)
    {
        if (!$connection instanceof Connection) {
            $connection = new Connection($connection);
        }
        $this->registerConnection($connection);
        $this->registerStatementPrototype($statementPrototype ?: new Statement());
        $this->registerResultPrototype($resultPrototype ?: new Result());
        if (is_array($features)) {
            foreach ($features as $name => $feature) {
                $this->addFeature($name, $feature);
            }
        } elseif ($features instanceof AbstractFeature) {
            $this->addFeature($features->getName(), $features);
        } elseif ($features === self::FEATURES_DEFAULT) {
            $this->setupDefaultFeatures();
        }
    }
    /**
     * @param Profiler\ProfilerInterface $profiler
     * @return Pdo
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        if ($this->connection instanceof Profiler\ProfilerAwareInterface) {
            $this->connection->setProfiler($profiler);
        }
        if ($this->statementPrototype instanceof Profiler\ProfilerAwareInterface) {
            $this->statementPrototype->setProfiler($profiler);
        }
        return $this;
    }
    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }
    /**
     * Register connection
     *
     * @param  Connection $connection
     * @return Pdo
     */
    public function registerConnection(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->setDriver($this);
        return $this;
    }
    /**
     * Register statement prototype
     *
     * @param Statement $statementPrototype
     */
    public function registerStatementPrototype(Statement $statementPrototype)
    {
        $this->statementPrototype = $statementPrototype;
        $this->statementPrototype->setDriver($this);
    }
    /**
     * Register result prototype
     *
     * @param Result $resultPrototype
     */
    public function registerResultPrototype(Result $resultPrototype)
    {
        $this->resultPrototype = $resultPrototype;
    }
    /**
     * Add feature
     *
     * @param string $name
     * @param AbstractFeature $feature
     * @return Pdo
     */
    public function addFeature($name, $feature)
    {
        if ($feature instanceof AbstractFeature) {
            $name = $feature->getName();
            // overwrite the name, just in case
            $feature->setDriver($this);
        }
        $this->features[$name] = $feature;
        return $this;
    }
    /**
     * Setup the default features for Pdo
     *
     * @return Pdo
     */
    public function setupDefaultFeatures()
    {
        if ($this->connection->getDriverName() == 'sqlite') {
            $this->addFeature(null, new Feature\SqliteRowCounter());
        }
        if ($this->connection->getDriverName() == 'oci') {
            $this->addFeature(null, new Feature\OracleRowCounter());
        }
        return $this;
    }
    /**
     * Get feature
     *
     * @param $name
     * @return AbstractFeature|false
     */
    public function getFeature($name)
    {
        if (isset($this->features[$name])) {
            return $this->features[$name];
        }
        return false;
    }
    /**
     * Get database platform name
     *
     * @param  string $nameFormat
     * @return string
     */
    public function getDatabasePlatformName($nameFormat = self::NAME_FORMAT_CAMELCASE)
    {
        $name = $this->getConnection()->getDriverName();
        if ($nameFormat == self::NAME_FORMAT_CAMELCASE) {
            switch ($name) {
                case 'pgsql':
                    return 'Postgresql';
                default:
                    return ucfirst($name);
            }
        } else {
            switch ($name) {
                case 'sqlite':
                    return 'SQLite';
                case 'mysql':
                    return 'MySQL';
                case 'pgsql':
                    return 'PostgreSQL';
                default:
                    return ucfirst($name);
            }
        }
    }
    /**
     * Check environment
     */
    public function checkEnvironment()
    {
        if (!extension_loaded('PDO')) {
            throw new Exception\RuntimeException('The PDO extension is required for this adapter but the extension is not loaded');
        }
    }
    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    /**
     * @param string|PDOStatement $sqlOrResource
     * @return Statement
     */
    public function createStatement($sqlOrResource = null)
    {
        $statement = clone $this->statementPrototype;
        if ($sqlOrResource instanceof PDOStatement) {
            $statement->setResource($sqlOrResource);
        } else {
            if (is_string($sqlOrResource)) {
                $statement->setSql($sqlOrResource);
            }
            if (!$this->connection->isConnected()) {
                $this->connection->connect();
            }
            $statement->initialize($this->connection->getResource());
        }
        return $statement;
    }
    /**
     * @param resource $resource
     * @param mixed $context
     * @return Result
     */
    public function createResult($resource, $context = null)
    {
        $result = clone $this->resultPrototype;
        $rowCount = null;
        // special feature, sqlite PDO counter
        if ($this->connection->getDriverName() == 'sqlite' && ($sqliteRowCounter = $this->getFeature('SqliteRowCounter')) && $resource->columnCount() > 0) {
            $rowCount = $sqliteRowCounter->getRowCountClosure($context);
        }
        // special feature, oracle PDO counter
        if ($this->connection->getDriverName() == 'oci' && ($oracleRowCounter = $this->getFeature('OracleRowCounter')) && $resource->columnCount() > 0) {
            $rowCount = $oracleRowCounter->getRowCountClosure($context);
        }
        $result->initialize($resource, $this->connection->getLastGeneratedValue(), $rowCount);
        return $result;
    }
    /**
     * @return array
     */
    public function getPrepareType()
    {
        return self::PARAMETERIZATION_NAMED;
    }
    /**
     * @param string $name
     * @param string|null $type
     * @return string
     */
    public function formatParameterName($name, $type = null)
    {
        if ($type == null && !is_numeric($name) || $type == self::PARAMETERIZATION_NAMED) {
            return ':' . $name;
        }
        return '?';
    }
    /**
     * @return mixed
     */
    public function getLastGeneratedValue()
    {
        return $this->connection->getLastGeneratedValue();
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver;

interface ConnectionInterface
{
    /**
     * Get current schema
     *
     * @return string
     */
    public function getCurrentSchema();
    /**
     * Get resource
     *
     * @return mixed
     */
    public function getResource();
    /**
     * Connect
     *
     * @return ConnectionInterface
     */
    public function connect();
    /**
     * Is connected
     *
     * @return bool
     */
    public function isConnected();
    /**
     * Disconnect
     *
     * @return ConnectionInterface
     */
    public function disconnect();
    /**
     * Begin transaction
     *
     * @return ConnectionInterface
     */
    public function beginTransaction();
    /**
     * Commit
     *
     * @return ConnectionInterface
     */
    public function commit();
    /**
     * Rollback
     *
     * @return ConnectionInterface
     */
    public function rollback();
    /**
     * Execute
     *
     * @param  string $sql
     * @return ResultInterface
     */
    public function execute($sql);
    /**
     * Get last generated id
     *
     * @param  null $name Ignored
     * @return integer
     */
    public function getLastGeneratedValue($name = null);
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver\Pdo;

use Zend\Db\Adapter\Driver\ConnectionInterface;
use Zend\Db\Adapter\Exception;
use Zend\Db\Adapter\Profiler;
class Connection implements ConnectionInterface, Profiler\ProfilerAwareInterface
{
    /**
     * @var Pdo
     */
    protected $driver = null;
    /**
     * @var Profiler\ProfilerInterface
     */
    protected $profiler = null;
    /**
     * @var string
     */
    protected $driverName = null;
    /**
     * @var array
     */
    protected $connectionParameters = array();
    /**
     * @var \PDO
     */
    protected $resource = null;
    /**
     * @var bool
     */
    protected $inTransaction = false;
    /**
     * Constructor
     *
     * @param array|\PDO|null $connectionParameters
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($connectionParameters = null)
    {
        if (is_array($connectionParameters)) {
            $this->setConnectionParameters($connectionParameters);
        } elseif ($connectionParameters instanceof \PDO) {
            $this->setResource($connectionParameters);
        } elseif (null !== $connectionParameters) {
            throw new Exception\InvalidArgumentException('$connection must be an array of parameters, a PDO object or null');
        }
    }
    /**
     * Set driver
     *
     * @param Pdo $driver
     * @return Connection
     */
    public function setDriver(Pdo $driver)
    {
        $this->driver = $driver;
        return $this;
    }
    /**
     * @param Profiler\ProfilerInterface $profiler
     * @return Connection
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }
    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }
    /**
     * Get driver name
     *
     * @return null|string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }
    /**
     * Set connection parameters
     *
     * @param array $connectionParameters
     * @return void
     */
    public function setConnectionParameters(array $connectionParameters)
    {
        $this->connectionParameters = $connectionParameters;
        if (isset($connectionParameters['dsn'])) {
            $this->driverName = substr($connectionParameters['dsn'], 0, strpos($connectionParameters['dsn'], ':'));
        } elseif (isset($connectionParameters['pdodriver'])) {
            $this->driverName = strtolower($connectionParameters['pdodriver']);
        } elseif (isset($connectionParameters['driver'])) {
            $this->driverName = strtolower(substr(str_replace(array('-', '_', ' '), '', $connectionParameters['driver']), 3));
        }
    }
    /**
     * Get connection parameters
     *
     * @return array
     */
    public function getConnectionParameters()
    {
        return $this->connectionParameters;
    }
    /**
     * Get current schema
     *
     * @return string
     */
    public function getCurrentSchema()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        switch ($this->driverName) {
            case 'mysql':
                $sql = 'SELECT DATABASE()';
                break;
            case 'sqlite':
                return 'main';
            case 'pgsql':
            default:
                $sql = 'SELECT CURRENT_SCHEMA';
                break;
        }
        /** @var $result \PDOStatement */
        $result = $this->resource->query($sql);
        if ($result instanceof \PDOStatement) {
            return $result->fetchColumn();
        }
        return false;
    }
    /**
     * Set resource
     *
     * @param  \PDO $resource
     * @return Connection
     */
    public function setResource(\PDO $resource)
    {
        $this->resource = $resource;
        $this->driverName = strtolower($this->resource->getAttribute(\PDO::ATTR_DRIVER_NAME));
        return $this;
    }
    /**
     * Get resource
     *
     * @return \PDO
     */
    public function getResource()
    {
        return $this->resource;
    }
    /**
     * Connect
     *
     * @return Connection
     * @throws Exception\InvalidConnectionParametersException
     * @throws Exception\RuntimeException
     */
    public function connect()
    {
        if ($this->resource) {
            return $this;
        }
        $dsn = $username = $password = $hostname = $database = null;
        $options = array();
        foreach ($this->connectionParameters as $key => $value) {
            switch (strtolower($key)) {
                case 'dsn':
                    $dsn = $value;
                    break;
                case 'driver':
                    $value = strtolower($value);
                    if (strpos($value, 'pdo') === 0) {
                        $pdoDriver = strtolower(substr(str_replace(array('-', '_', ' '), '', $value), 3));
                    }
                    break;
                case 'pdodriver':
                    $pdoDriver = (string) $value;
                    break;
                case 'user':
                case 'username':
                    $username = (string) $value;
                    break;
                case 'pass':
                case 'password':
                    $password = (string) $value;
                    break;
                case 'host':
                case 'hostname':
                    $hostname = (string) $value;
                    break;
                case 'port':
                    $port = (int) $value;
                    break;
                case 'database':
                case 'dbname':
                    $database = (string) $value;
                    break;
                case 'driver_options':
                case 'options':
                    $value = (array) $value;
                    $options = array_diff_key($options, $value) + $value;
                    break;
                default:
                    $options[$key] = $value;
                    break;
            }
        }
        if (!isset($dsn) && isset($pdoDriver)) {
            $dsn = array();
            switch ($pdoDriver) {
                case 'sqlite':
                    $dsn[] = $database;
                    break;
                default:
                    if (isset($database)) {
                        $dsn[] = "dbname={$database}";
                    }
                    if (isset($hostname)) {
                        $dsn[] = "host={$hostname}";
                    }
                    if (isset($port)) {
                        $dsn[] = "port={$port}";
                    }
                    break;
            }
            $dsn = $pdoDriver . ':' . implode(';', $dsn);
        } elseif (!isset($dsn)) {
            throw new Exception\InvalidConnectionParametersException('A dsn was not provided or could not be constructed from your parameters', $this->connectionParameters);
        }
        try {
            $this->resource = new \PDO($dsn, $username, $password, $options);
            $this->resource->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->driverName = strtolower($this->resource->getAttribute(\PDO::ATTR_DRIVER_NAME));
        } catch (\PDOException $e) {
            $code = $e->getCode();
            if (!is_long($code)) {
                $code = null;
            }
            throw new Exception\RuntimeException('Connect Error: ' . $e->getMessage(), $code, $e);
        }
        return $this;
    }
    /**
     * Is connected
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->resource instanceof \PDO;
    }
    /**
     * Disconnect
     *
     * @return Connection
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->resource = null;
        }
        return $this;
    }
    /**
     * Begin transaction
     *
     * @return Connection
     */
    public function beginTransaction()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        $this->resource->beginTransaction();
        $this->inTransaction = true;
        return $this;
    }
    /**
     * Commit
     *
     * @return Connection
     */
    public function commit()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        $this->resource->commit();
        $this->inTransaction = false;
        return $this;
    }
    /**
     * Rollback
     *
     * @return Connection
     * @throws Exception\RuntimeException
     */
    public function rollback()
    {
        if (!$this->isConnected()) {
            throw new Exception\RuntimeException('Must be connected before you can rollback');
        }
        if (!$this->inTransaction) {
            throw new Exception\RuntimeException('Must call beginTransaction() before you can rollback');
        }
        $this->resource->rollBack();
        return $this;
    }
    /**
     * Execute
     *
     * @param $sql
     * @return Result
     * @throws Exception\InvalidQueryException
     */
    public function execute($sql)
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        if ($this->profiler) {
            $this->profiler->profilerStart($sql);
        }
        $resultResource = $this->resource->query($sql);
        if ($this->profiler) {
            $this->profiler->profilerFinish($sql);
        }
        if ($resultResource === false) {
            $errorInfo = $this->resource->errorInfo();
            throw new Exception\InvalidQueryException($errorInfo[2]);
        }
        $result = $this->driver->createResult($resultResource, $sql);
        return $result;
    }
    /**
     * Prepare
     *
     * @param string $sql
     * @return Statement
     */
    public function prepare($sql)
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        $statement = $this->driver->createStatement($sql);
        return $statement;
    }
    /**
     * Get last generated id
     *
     * @param string $name
     * @return integer|null|false
     */
    public function getLastGeneratedValue($name = null)
    {
        if ($name === null && $this->driverName == 'pgsql') {
            return null;
        }
        try {
            return $this->resource->lastInsertId($name);
        } catch (\Exception $e) {
            
        }
        return false;
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter;

interface StatementContainerInterface
{
    /**
     * Set sql
     *
     * @param $sql
     * @return mixed
     */
    public function setSql($sql);
    /**
     * Get sql
     *
     * @return mixed
     */
    public function getSql();
    /**
     * Set parameter container
     *
     * @param ParameterContainer $parameterContainer
     * @return mixed
     */
    public function setParameterContainer(ParameterContainer $parameterContainer);
    /**
     * Get parameter container
     *
     * @return mixed
     */
    public function getParameterContainer();
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver;

use Zend\Db\Adapter\StatementContainerInterface;
interface StatementInterface extends StatementContainerInterface
{
    /**
     * Get resource
     *
     * @return resource
     */
    public function getResource();
    /**
     * Prepare sql
     *
     * @param string $sql
     */
    public function prepare($sql = null);
    /**
     * Check if is prepared
     *
     * @return bool
     */
    public function isPrepared();
    /**
     * Execute
     *
     * @param null $parameters
     * @return ResultInterface
     */
    public function execute($parameters = null);
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver\Pdo;

use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Exception;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Profiler;
class Statement implements StatementInterface, Profiler\ProfilerAwareInterface
{
    /**
     * @var \PDO
     */
    protected $pdo = null;
    /**
     * @var Profiler\ProfilerInterface
     */
    protected $profiler = null;
    /**
     * @var Pdo
     */
    protected $driver = null;
    /**
     *
     * @var string
     */
    protected $sql = '';
    /**
     *
     * @var bool
     */
    protected $isQuery = null;
    /**
     *
     * @var ParameterContainer
     */
    protected $parameterContainer = null;
    /**
     * @var bool
     */
    protected $parametersBound = false;
    /**
     * @var \PDOStatement
     */
    protected $resource = null;
    /**
     *
     * @var bool
     */
    protected $isPrepared = false;
    /**
     * Set driver
     *
     * @param  Pdo $driver
     * @return Statement
     */
    public function setDriver(Pdo $driver)
    {
        $this->driver = $driver;
        return $this;
    }
    /**
     * @param Profiler\ProfilerInterface $profiler
     * @return Statement
     */
    public function setProfiler(Profiler\ProfilerInterface $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }
    /**
     * @return null|Profiler\ProfilerInterface
     */
    public function getProfiler()
    {
        return $this->profiler;
    }
    /**
     * Initialize
     *
     * @param  \PDO $connectionResource
     * @return Statement
     */
    public function initialize(\PDO $connectionResource)
    {
        $this->pdo = $connectionResource;
        return $this;
    }
    /**
     * Set resource
     *
     * @param  \PDOStatement $pdoStatement
     * @return Statement
     */
    public function setResource(\PDOStatement $pdoStatement)
    {
        $this->resource = $pdoStatement;
        return $this;
    }
    /**
     * Get resource
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
    /**
     * Set sql
     *
     * @param string $sql
     * @return Statement
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }
    /**
     * Get sql
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }
    /**
     * @param ParameterContainer $parameterContainer
     * @return Statement
     */
    public function setParameterContainer(ParameterContainer $parameterContainer)
    {
        $this->parameterContainer = $parameterContainer;
        return $this;
    }
    /**
     * @return ParameterContainer
     */
    public function getParameterContainer()
    {
        return $this->parameterContainer;
    }
    /**
     * @param string $sql
     * @throws Exception\RuntimeException
     */
    public function prepare($sql = null)
    {
        if ($this->isPrepared) {
            throw new Exception\RuntimeException('This statement has been prepared already');
        }
        if ($sql == null) {
            $sql = $this->sql;
        }
        $this->resource = $this->pdo->prepare($sql);
        if ($this->resource === false) {
            $error = $this->pdo->errorInfo();
            throw new Exception\RuntimeException($error[2]);
        }
        $this->isPrepared = true;
    }
    /**
     * @return bool
     */
    public function isPrepared()
    {
        return $this->isPrepared;
    }
    /**
     * @param mixed $parameters
     * @throws Exception\InvalidQueryException
     * @return Result
     */
    public function execute($parameters = null)
    {
        if (!$this->isPrepared) {
            $this->prepare();
        }
        /** START Standard ParameterContainer Merging Block */
        if (!$this->parameterContainer instanceof ParameterContainer) {
            if ($parameters instanceof ParameterContainer) {
                $this->parameterContainer = $parameters;
                $parameters = null;
            } else {
                $this->parameterContainer = new ParameterContainer();
            }
        }
        if (is_array($parameters)) {
            $this->parameterContainer->setFromArray($parameters);
        }
        if ($this->parameterContainer->count() > 0) {
            $this->bindParametersFromContainer();
        }
        /** END Standard ParameterContainer Merging Block */
        if ($this->profiler) {
            $this->profiler->profilerStart($this);
        }
        try {
            $this->resource->execute();
        } catch (\PDOException $e) {
            if ($this->profiler) {
                $this->profiler->profilerFinish();
            }
            throw new Exception\InvalidQueryException('Statement could not be executed', null, $e);
        }
        if ($this->profiler) {
            $this->profiler->profilerFinish();
        }
        $result = $this->driver->createResult($this->resource, $this);
        return $result;
    }
    /**
     * Bind parameters from container
     */
    protected function bindParametersFromContainer()
    {
        if ($this->parametersBound) {
            return;
        }
        $parameters = $this->parameterContainer->getNamedArray();
        foreach ($parameters as $name => &$value) {
            $type = \PDO::PARAM_STR;
            if ($this->parameterContainer->offsetHasErrata($name)) {
                switch ($this->parameterContainer->offsetGetErrata($name)) {
                    case ParameterContainer::TYPE_INTEGER:
                        $type = \PDO::PARAM_INT;
                        break;
                    case ParameterContainer::TYPE_NULL:
                        $type = \PDO::PARAM_NULL;
                        break;
                    case ParameterContainer::TYPE_LOB:
                        $type = \PDO::PARAM_LOB;
                        break;
                    case is_bool($value):
                        $type = \PDO::PARAM_BOOL;
                        break;
                }
            }
            // parameter is named or positional, value is reference
            $parameter = is_int($name) ? $name + 1 : $name;
            $this->resource->bindParam($parameter, $value, $type);
        }
    }
    /**
     * Perform a deep clone
     * @return Statement A cloned statement
     */
    public function __clone()
    {
        $this->isPrepared = false;
        $this->parametersBound = false;
        $this->resource = null;
        if ($this->parameterContainer) {
            $this->parameterContainer = clone $this->parameterContainer;
        }
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver;

use Countable;
use Iterator;
interface ResultInterface extends Countable, Iterator
{
    /**
     * Force buffering
     *
     * @return void
     */
    public function buffer();
    /**
     * Check if is buffered
     *
     * @return bool|null
     */
    public function isBuffered();
    /**
     * Is query result?
     *
     * @return bool
     */
    public function isQueryResult();
    /**
     * Get affected rows
     *
     * @return integer
     */
    public function getAffectedRows();
    /**
     * Get generated value
     *
     * @return mixed|null
     */
    public function getGeneratedValue();
    /**
     * Get the resource
     *
     * @return mixed
     */
    public function getResource();
    /**
     * Get field count
     *
     * @return integer
     */
    public function getFieldCount();
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver\Pdo;

use Iterator;
use PDOStatement;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Adapter\Exception;
class Result implements Iterator, ResultInterface
{
    const STATEMENT_MODE_SCROLLABLE = 'scrollable';
    const STATEMENT_MODE_FORWARD = 'forward';
    /**
     *
     * @var string
     */
    protected $statementMode = self::STATEMENT_MODE_FORWARD;
    /**
     * @var \PDOStatement
     */
    protected $resource = null;
    /**
     * @var array Result options
     */
    protected $options;
    /**
     * Is the current complete?
     * @var bool
     */
    protected $currentComplete = false;
    /**
     * Track current item in recordset
     * @var mixed
     */
    protected $currentData = null;
    /**
     * Current position of scrollable statement
     * @var int
     */
    protected $position = -1;
    /**
     * @var mixed
     */
    protected $generatedValue = null;
    /**
     * @var null
     */
    protected $rowCount = null;
    /**
     * Initialize
     *
     * @param  PDOStatement $resource
     * @param               $generatedValue
     * @param  int          $rowCount
     * @return Result
     */
    public function initialize(PDOStatement $resource, $generatedValue, $rowCount = null)
    {
        $this->resource = $resource;
        $this->generatedValue = $generatedValue;
        $this->rowCount = $rowCount;
        return $this;
    }
    /**
     * @return null
     */
    public function buffer()
    {
        return null;
    }
    /**
     * @return bool|null
     */
    public function isBuffered()
    {
        return false;
    }
    /**
     * Get resource
     *
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
    /**
     * Get the data
     * @return array
     */
    public function current()
    {
        if ($this->currentComplete) {
            return $this->currentData;
        }
        $this->currentData = $this->resource->fetch(\PDO::FETCH_ASSOC);
        return $this->currentData;
    }
    /**
     * Next
     *
     * @return mixed
     */
    public function next()
    {
        $this->currentData = $this->resource->fetch(\PDO::FETCH_ASSOC);
        $this->currentComplete = true;
        $this->position++;
        return $this->currentData;
    }
    /**
     * Key
     *
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }
    /**
     * @throws Exception\RuntimeException
     * @return void
     */
    public function rewind()
    {
        if ($this->statementMode == self::STATEMENT_MODE_FORWARD && $this->position > 0) {
            throw new Exception\RuntimeException('This result is a forward only result set, calling rewind() after moving forward is not supported');
        }
        $this->currentData = $this->resource->fetch(\PDO::FETCH_ASSOC);
        $this->currentComplete = true;
        $this->position = 0;
    }
    /**
     * Valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->currentData !== false;
    }
    /**
     * Count
     *
     * @return integer
     */
    public function count()
    {
        if (is_int($this->rowCount)) {
            return $this->rowCount;
        }
        if ($this->rowCount instanceof \Closure) {
            $this->rowCount = (int) call_user_func($this->rowCount);
        } else {
            $this->rowCount = (int) $this->resource->rowCount();
        }
        return $this->rowCount;
    }
    /**
     * @return int
     */
    public function getFieldCount()
    {
        return $this->resource->columnCount();
    }
    /**
     * Is query result
     *
     * @return bool
     */
    public function isQueryResult()
    {
        return $this->resource->columnCount() > 0;
    }
    /**
     * Get affected rows
     *
     * @return integer
     */
    public function getAffectedRows()
    {
        return $this->resource->rowCount();
    }
    /**
     * @return mixed|null
     */
    public function getGeneratedValue()
    {
        return $this->generatedValue;
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver\Feature;

use Zend\Db\Adapter\Driver\DriverInterface;
abstract class AbstractFeature
{
    /**
     * @var DriverInterface
     */
    protected $driver = null;
    /**
     * Set driver
     *
     * @param DriverInterface $driver
     * @return void
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }
    /**
     * Get name
     *
     * @return string
     */
    public abstract function getName();
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Driver\Pdo\Feature;

use Zend\Db\Adapter\Driver\Feature\AbstractFeature;
use Zend\Db\Adapter\Driver\Pdo;
/**
 * SqliteRowCounter
 */
class SqliteRowCounter extends AbstractFeature
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'SqliteRowCounter';
    }
    /**
     * @param \Zend\Db\Adapter\Driver\Pdo\Statement $statement
     * @return int
     */
    public function getCountForStatement(Pdo\Statement $statement)
    {
        $countStmt = clone $statement;
        $sql = $statement->getSql();
        if ($sql == '' || stripos($sql, 'select') === false) {
            return null;
        }
        $countSql = 'SELECT COUNT(*) as "count" FROM (' . $sql . ')';
        $countStmt->prepare($countSql);
        $result = $countStmt->execute();
        $countRow = $result->getResource()->fetch(\PDO::FETCH_ASSOC);
        unset($statement, $result);
        return $countRow['count'];
    }
    /**
     * @param $sql
     * @return null|int
     */
    public function getCountForSql($sql)
    {
        if (!stripos($sql, 'select')) {
            return null;
        }
        $countSql = 'SELECT COUNT(*) as count FROM (' . $sql . ')';
        /** @var $pdo \PDO */
        $pdo = $this->pdoDriver->getConnection()->getResource();
        $result = $pdo->query($countSql);
        $countRow = $result->fetch(\PDO::FETCH_ASSOC);
        return $countRow['count'];
    }
    /**
     * @param $context
     * @return closure
     */
    public function getRowCountClosure($context)
    {
        $sqliteRowCounter = $this;
        return function () use($sqliteRowCounter, $context) {
            /** @var $sqliteRowCounter SqliteRowCounter */
            return $context instanceof Pdo\Statement ? $sqliteRowCounter->getCountForStatement($context) : $sqliteRowCounter->getCountForSql($context);
        };
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Platform;

interface PlatformInterface
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName();
    /**
     * Get quote identifier symbol
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol();
    /**
     * Quote identifier
     *
     * @param  string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier);
    /**
     * Quote identifier chain
     *
     * @param string|string[] $identifierChain
     * @return string
     */
    public function quoteIdentifierChain($identifierChain);
    /**
     * Get quote value symbol
     *
     * @return string
     */
    public function getQuoteValueSymbol();
    /**
     * Quote value
     *
     * @param  string $value
     * @return string
     */
    public function quoteValue($value);
    /**
     * Quote value list
     *
     * @param string|string[] $valueList
     * @return string
     */
    public function quoteValueList($valueList);
    /**
     * Get identifier separator
     *
     * @return string
     */
    public function getIdentifierSeparator();
    /**
     * Quote identifier in fragment
     *
     * @param  string $identifier
     * @param  array $safeWords
     * @return string
     */
    public function quoteIdentifierInFragment($identifier, array $additionalSafeWords = array());
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\Adapter\Platform;

class Sqlite implements PlatformInterface
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'SQLite';
    }
    /**
     * Get quote identifier symbol
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return '"';
    }
    /**
     * Quote identifier
     *
     * @param  string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return '"' . str_replace('"', '\\' . '"', $identifier) . '"';
    }
    /**
     * Quote identifier chain
     *
     * @param string|string[] $identifierChain
     * @return string
     */
    public function quoteIdentifierChain($identifierChain)
    {
        $identifierChain = str_replace('"', '\\"', $identifierChain);
        if (is_array($identifierChain)) {
            $identifierChain = implode('"."', $identifierChain);
        }
        return '"' . $identifierChain . '"';
    }
    /**
     * Get quote value symbol
     *
     * @return string
     */
    public function getQuoteValueSymbol()
    {
        return '\'';
    }
    /**
     * Quote value
     *
     * @param  string $value
     * @return string
     */
    public function quoteValue($value)
    {
        return '\'' . str_replace('\'', '\\' . '\'', $value) . '\'';
    }
    /**
     * Quote value list
     *
     * @param string|string[] $valueList
     * @return string
     */
    public function quoteValueList($valueList)
    {
        $valueList = str_replace('\'', '\\' . '\'', $valueList);
        if (is_array($valueList)) {
            $valueList = implode('\', \'', $valueList);
        }
        return '\'' . $valueList . '\'';
    }
    /**
     * Get identifier separator
     *
     * @return string
     */
    public function getIdentifierSeparator()
    {
        return '.';
    }
    /**
     * Quote identifier in fragment
     *
     * @param  string $identifier
     * @param  array $safeWords
     * @return string
     */
    public function quoteIdentifierInFragment($identifier, array $safeWords = array())
    {
        $parts = preg_split('#([\\.\\s\\W])#', $identifier, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if ($safeWords) {
            $safeWords = array_flip($safeWords);
            $safeWords = array_change_key_case($safeWords, CASE_LOWER);
        }
        foreach ($parts as $i => $part) {
            if ($safeWords && isset($safeWords[strtolower($part)])) {
                continue;
            }
            switch ($part) {
                case ' ':
                case '.':
                case '*':
                case 'AS':
                case 'As':
                case 'aS':
                case 'as':
                    break;
                default:
                    $parts[$i] = '"' . str_replace('"', '\\' . '"', $part) . '"';
            }
        }
        return implode('', $parts);
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\ResultSet;

interface ResultSetInterface extends \Traversable, \Countable
{
    /**
     * Can be anything traversable|array
     * @abstract
     * @param $dataSource
     * @return mixed
     */
    public function initialize($dataSource);
    /**
     * Field terminology is more correct as information coming back
     * from the database might be a column, and/or the result of an
     * operation or intersection of some data
     * @abstract
     * @return mixed
     */
    public function getFieldCount();
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\ResultSet;

use ArrayIterator;
use ArrayObject;
use Countable;
use Iterator;
use IteratorAggregate;
use Zend\Db\Adapter\Driver\ResultInterface;
abstract class AbstractResultSet implements Iterator, ResultSetInterface
{
    /**
     * if -1, datasource is already buffered
     * if -2, implicitly disabling buffering in ResultSet
     * if false, explicitly disabled
     * if null, default state - nothing, but can buffer until iteration started
     * if array, already buffering
     * @var mixed
     */
    protected $buffer = null;
    /**
     * @var null|int
     */
    protected $count = null;
    /**
     * @var Iterator|IteratorAggregate|ResultInterface
     */
    protected $dataSource = null;
    /**
     * @var int
     */
    protected $fieldCount = null;
    /**
     * @var int
     */
    protected $position = 0;
    /**
     * Set the data source for the result set
     *
     * @param  Iterator|IteratorAggregate|ResultInterface $dataSource
     * @return ResultSet
     * @throws Exception\InvalidArgumentException
     */
    public function initialize($dataSource)
    {
        if ($dataSource instanceof ResultInterface) {
            $this->count = $dataSource->count();
            $this->fieldCount = $dataSource->getFieldCount();
            $this->dataSource = $dataSource;
            if ($dataSource->isBuffered()) {
                $this->buffer = -1;
            }
            return $this;
        }
        if (is_array($dataSource)) {
            // its safe to get numbers from an array
            $first = current($dataSource);
            reset($dataSource);
            $this->count = count($dataSource);
            $this->fieldCount = count($first);
            $this->dataSource = new ArrayIterator($dataSource);
            $this->buffer = -1;
        } elseif ($dataSource instanceof IteratorAggregate) {
            $this->dataSource = $dataSource->getIterator();
        } elseif ($dataSource instanceof Iterator) {
            $this->dataSource = $dataSource;
        } else {
            throw new Exception\InvalidArgumentException('DataSource provided is not an array, nor does it implement Iterator or IteratorAggregate');
        }
        if ($this->count == null && $this->dataSource instanceof Countable) {
            $this->count = $this->dataSource->count();
        }
        return $this;
    }
    public function buffer()
    {
        if ($this->buffer === -2) {
            throw new Exception\RuntimeException('Buffering must be enabled before iteration is started');
        } elseif ($this->buffer === null) {
            $this->buffer = array();
        }
        return $this;
    }
    public function isBuffered()
    {
        if ($this->buffer === -1 || is_array($this->buffer)) {
            return true;
        }
        return false;
    }
    /**
     * Get the data source used to create the result set
     *
     * @return null|Iterator
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }
    /**
     * Retrieve count of fields in individual rows of the result set
     *
     * @return int
     */
    public function getFieldCount()
    {
        if (null !== $this->fieldCount) {
            return $this->fieldCount;
        }
        $dataSource = $this->getDataSource();
        if (null === $dataSource) {
            return 0;
        }
        $dataSource->rewind();
        if (!$dataSource->valid()) {
            $this->fieldCount = 0;
            return 0;
        }
        $row = $dataSource->current();
        if (is_object($row) && $row instanceof Countable) {
            $this->fieldCount = $row->count();
            return $this->fieldCount;
        }
        $row = (array) $row;
        $this->fieldCount = count($row);
        return $this->fieldCount;
    }
    /**
     * Iterator: move pointer to next item
     *
     * @return void
     */
    public function next()
    {
        if ($this->buffer === null) {
            $this->buffer = -2;
        }
        $this->dataSource->next();
        $this->position++;
    }
    /**
     * Iterator: retrieve current key
     *
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }
    /**
     * Iterator: get current item
     *
     * @return array
     */
    public function current()
    {
        if ($this->buffer === null) {
            $this->buffer = -2;
        } elseif (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return $this->buffer[$this->position];
        }
        $data = $this->dataSource->current();
        if (is_array($this->buffer)) {
            $this->buffer[$this->position] = $data;
        }
        return $data;
    }
    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    public function valid()
    {
        if (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return true;
        }
        if ($this->dataSource instanceof Iterator) {
            return $this->dataSource->valid();
        } else {
            $key = key($this->dataSource);
            return $key !== null;
        }
    }
    /**
     * Iterator: rewind
     *
     * @return void
     */
    public function rewind()
    {
        if (!is_array($this->buffer)) {
            if ($this->dataSource instanceof Iterator) {
                $this->dataSource->rewind();
            } else {
                reset($this->dataSource);
            }
        }
        $this->position = 0;
    }
    /**
     * Countable: return count of rows
     *
     * @return int
     */
    public function count()
    {
        if ($this->count !== null) {
            return $this->count;
        }
        $this->count = count($this->dataSource);
        return $this->count;
    }
    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray()
    {
        $return = array();
        foreach ($this as $row) {
            if (is_array($row)) {
                $return[] = $row;
            } elseif (method_exists($row, 'toArray')) {
                $return[] = $row->toArray();
            } elseif ($row instanceof ArrayObject) {
                $return[] = $row->getArrayCopy();
            } else {
                throw new Exception\RuntimeException('Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array');
            }
        }
        return $return;
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Db\ResultSet;

use ArrayObject;
class ResultSet extends AbstractResultSet
{
    const TYPE_ARRAYOBJECT = 'arrayobject';
    const TYPE_ARRAY = 'array';
    /**
     * Allowed return types
     *
     * @var array
     */
    protected $allowedReturnTypes = array(self::TYPE_ARRAYOBJECT, self::TYPE_ARRAY);
    /**
     * @var ArrayObject
     */
    protected $arrayObjectPrototype = null;
    /**
     * Return type to use when returning an object from the set
     *
     * @var ResultSet::TYPE_ARRAYOBJECT|ResultSet::TYPE_ARRAY
     */
    protected $returnType = self::TYPE_ARRAYOBJECT;
    /**
     * Constructor
     *
     * @param string           $returnType
     * @param null|ArrayObject $arrayObjectPrototype
     */
    public function __construct($returnType = self::TYPE_ARRAYOBJECT, $arrayObjectPrototype = null)
    {
        $this->returnType = in_array($returnType, array(self::TYPE_ARRAY, self::TYPE_ARRAYOBJECT)) ? $returnType : self::TYPE_ARRAYOBJECT;
        if ($this->returnType === self::TYPE_ARRAYOBJECT) {
            $this->setArrayObjectPrototype($arrayObjectPrototype ?: new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS));
        }
    }
    /**
     * Set the row object prototype
     *
     * @param  ArrayObject $arrayObjectPrototype
     * @throws Exception\InvalidArgumentException
     * @return ResultSet
     */
    public function setArrayObjectPrototype($arrayObjectPrototype)
    {
        if (!is_object($arrayObjectPrototype) || !$arrayObjectPrototype instanceof ArrayObject && !method_exists($arrayObjectPrototype, 'exchangeArray')) {
            throw new Exception\InvalidArgumentException('Object must be of type ArrayObject, or at least implement exchangeArray');
        }
        $this->arrayObjectPrototype = $arrayObjectPrototype;
        return $this;
    }
    /**
     * Get the row object prototype
     *
     * @return ArrayObject
     */
    public function getArrayObjectPrototype()
    {
        return $this->arrayObjectPrototype;
    }
    /**
     * Get the return type to use when returning objects from the set
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }
    /**
     * @return array|\ArrayObject|null
     */
    public function current()
    {
        $data = parent::current();
        if ($this->returnType === self::TYPE_ARRAYOBJECT && is_array($data)) {
            /** @var $ao ArrayObject */
            $ao = clone $this->arrayObjectPrototype;
            if ($ao instanceof ArrayObject || method_exists($ao, 'exchangeArray')) {
                $ao->exchangeArray($data);
            }
            return $ao;
        }
        return $data;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package    BEAR.Sunday
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Debug\ExceptionHandle;

use BEAR\Resource\Exception\BadRequest;
use Ray\Di\InjectorInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Exception\NotBound;
use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Exception\ResourceNotFound;
use BEAR\Resource\Exception\MethodNotAllowed;
use BEAR\Resource\Exception\Parameter;
use BEAR\Resource\Exception\Scheme;
use BEAR\Resource\Exception\Uri;
use BEAR\Sunday\Extension\WebResponse\ResponseInterface;
use BEAR\Sunday\Inject\LogDirInject;
use Exception;
use Ray\Di\Exception\Binding;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
/**
 * Exception handler for development
 *
 * @package    BEAR.Sunday
 * @subpackage Exception
 */
final class ExceptionHandler implements ExceptionHandlerInterface
{
    use LogDirInject;
    /**
     * Response
     *
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var ResourceObject
     */
    private $errorPage;
    /**
     * @var InjectorInterface
     */
    private $injector;
    /**
     * @var string
     */
    private $viewTemplate;
    /**
     * Error message
     *
     * @var array
     */
    private $message = array('ResourceNotFound' => 'The requested URI was not found on this service.', 'BadRequest' => 'You sent a request that this service could not understand.', 'Parameter' => 'You sent a request that query is not valid.', 'Scheme' => 'You sent a request that scheme is not valid.', 'MethodNotAllowed' => 'The requested method is not allowed for this URI.');
    /**
     * Set message
     *
     * @param array $message
     *
     * @Inject(optional = true);
     */
    public function setMessage(array $message)
    {
        $this->message = $message;
    }
    /**
     * Set Injector for logging
     *
     * @param \Ray\Di\InjectorInterface $injector
     *
     * @Inject(optional = true);
     */
    public function setModule(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }
    /**
     * Set response
     *
     * @param mixed             $exceptionTpl
     * @param ResponseInterface $response
     * @param ResourceObject    $errorPage
     *
     * @Inject
     * @Named("exceptionTpl=exceptionTpl,errorPage=errorPage")
     */
    public function __construct($exceptionTpl = null, ResponseInterface $response, ResourceObject $errorPage = null)
    {
        $this->viewTemplate = $exceptionTpl;
        $this->response = $response;
        $this->errorPage = $errorPage ?: new ErrorPage();
    }
    /**
     * (non-PHPdoc)
     *
     * @see BEAR\Package\Exception.ExceptionHandlerInterface::handle()
     */
    public function handle(Exception $e)
    {
        $page = $this->buildErrorPage($e, $this->errorPage);
        $id = $page->headers['X-EXCEPTION-ID'];
        $this->writeExceptionLog($e, $id);
        $this->response->setResource($page)->render()->send();
        die(1);
    }
    /**
     * Return error page
     *
     * @param                               $e
     * @param \BEAR\Resource\AbstractObject $response
     *
     * @return \BEAR\Resource\AbstractObject
     * @throws
     */
    private function buildErrorPage($e, ResourceObject $response)
    {
        $exceptionId = 'e' . $response->code . '-' . substr(md5((string) $e), 0, 5);
        try {
            throw $e;
        } catch (ResourceNotFound $e) {
            $response->code = 404;
            $response->view = $this->message['ResourceNotFound'];
            goto NOT_FOUND;
        } catch (Parameter $e) {
            $response->code = 400;
            $response->view = $this->message['Parameter'];
            goto BAD_REQUEST;
        } catch (Scheme $e) {
            $response->code = 400;
            $response->view = $this->message['Scheme'];
            goto BAD_REQUEST;
        } catch (MethodNotAllowed $e) {
            $response->code = 405;
            $response->view = $this->message['MethodNotAllowed'];
            goto METHOD_NOT_ALLOWED;
        } catch (Binding $e) {
            goto INVALID_BINDING;
        } catch (Uri $e) {
            $response->code = 400;
            goto INVALID_URI;
        } catch (BadRequest $e) {
            $response->code = 400;
            $response->view = $this->message['BadRequest'];
            goto METHOD_NOT_ALLOWED;
        } catch (Exception $e) {
            $response->view = "Internal error occurred ({$exceptionId})";
            goto SERVER_ERROR;
        }
        INVALID_BINDING:
        SERVER_ERROR:
        $response->code = 500;
        NOT_FOUND:
        BAD_REQUEST:
        METHOD_NOT_ALLOWED:
        INVALID_URI:
        if (PHP_SAPI === 'cli') {
            
        } else {
            $response->view = $this->getView($e);
        }
        $response->headers['X-EXCEPTION-CLASS'] = get_class($e);
        $response->headers['X-EXCEPTION-MESSAGE'] = str_replace(PHP_EOL, ' ', $e->getMessage());
        $response->headers['X-EXCEPTION-CODE-FILE-LINE'] = '(' . $e->getCode() . ') ' . $e->getFile() . ':' . $e->getLine();
        $previous = $e->getPrevious() ? get_class($e->getPrevious()) . ': ' . str_replace(PHP_EOL, ' ', $e->getPrevious()->getMessage()) : '-';
        $response->headers['X-EXCEPTION-PREVIOUS'] = $previous;
        $response->headers['X-EXCEPTION-ID'] = $exceptionId;
        $response->headers['X-EXCEPTION-ID-FILE'] = $this->getLogFilePath($exceptionId);
        return $response;
    }
    /**
     * Return view
     *
     * @param \Exception $e
     *
     * @return string
     */
    private function getView(\Exception $e)
    {
        // exception screen in develop
        if (isset($this->injector)) {
            $view['dependency_bindings'] = (string) $this->injector;
            $view['modules'] = $this->injector->getModule()->modules;
        } elseif ($e instanceof NotBound) {
            $view['dependency_bindings'] = (string) $e->module;
            $view['modules'] = $e->module;
        } else {
            $view['dependency_bindings'] = 'n/a';
            $view['modules'] = 'n/a';
        }
        $html = $this->getViewTemplate($e, $view);
        return $html;
    }
    /**
     * Write exception logs
     *
     * @param Exception $e
     * @param string    $exceptionId
     */
    public function writeExceptionLog(Exception $e, $exceptionId)
    {
        $data = (string) $e;
        $previousE = $e->getPrevious();
        if ($previousE) {
            $data .= PHP_EOL . PHP_EOL . '-- Previous Exception --' . PHP_EOL . PHP_EOL;
            $data .= $previousE->getTraceAsString();
        }
        $data .= PHP_EOL . PHP_EOL . '-- Bindings --' . PHP_EOL . (string) $this->injector;
        $file = $this->getLogFilePath($exceptionId);
        if (is_writable($this->logDir)) {
            file_put_contents($file, $data);
        } else {
            error_log("{$file} is not writable");
        }
    }
    /**
     * Return log file path
     *
     * @param $exceptionId
     *
     * @return string
     */
    private function getLogFilePath($exceptionId)
    {
        return "{$this->logDir}/{$exceptionId}.log";
    }
    /**
     * @param \Exception $e
     * @param array      $view
     *
     * @return mixed
     */
    private function getViewTemplate(\Exception $e, array $view = array('dependency_bindings' => '', 'modules' => ''))
    {
        return require $this->viewTemplate;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Inject;

use Guzzle\Log\LogAdapterInterface;
/**
 * Inject logger
 *
 * @package    BEAR.Sunday
 * @subpackage Inject
 */
trait LogInject
{
    /**
     * Logger
     *
     * @var LogAdapterInterface
     */
    private $log;
    /**
     * Logger setter
     *
     * @param LogAdapterInterface $log
     *
     * @return void
     * @Ray\Di\Di\Inject
     */
    public function setLog(LogAdapterInterface $log)
    {
        $this->log = $log;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\WebResponse;

use BEAR\Sunday\Extension\ConsoleOutput\ConsoleOutputInterface;
use BEAR\Sunday\Extension\WebResponse\ResponseInterface;
use BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface as AppLogger;
use BEAR\Sunday\Exception\InvalidResourceType;
use BEAR\Sunday\Inject\LogInject;
use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Resource\Logger;
use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject as Page;
use Symfony\Component\HttpFoundation\Response;
use Ray\Aop\Weave;
use Ray\Di\Di\Inject;
use Exception;
/**
 * Output with using Symfony HttpFoundation
 *
 * @package    BEAR.Sunday
 * @subpackage Web
 */
final class HttpFoundation implements ResponseInterface
{
    use LogInject;
    /**
     * Exception
     *
     * @var Exception
     */
    private $e;
    /**
     * Resource object
     *
     * @var \BEAR\Resource\AbstractObject
     */
    private $resource;
    /**
     * Response resource object
     *
     * @var Response
     */
    private $response;
    /**
     * @var int
     */
    private $code;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var string
     */
    private $body;
    /**
     * @var string
     */
    private $view;
    /**
     * @var ConsoleOutputInterface
     */
    private $consoleOutput;
    /**
     * @var AppLogger
     */
    private $appLogger;
    /**
     * Set application logger
     *
     * @param AppLogger $appLogger
     *
     * @Inject
     */
    public function setAppLogger(AppLogger $appLogger)
    {
        $this->appLogger = $appLogger;
    }
    /**
     * Constructor
     *
     * @param ConsoleOutputInterface $consoleOutput
     *
     * @Inject
     */
    public function __construct(ConsoleOutputInterface $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }
    /**
     * Set Resource
     *
     * @param mixed $resource BEAR\Resource\Object | Ray\Aop\Weaver $resource
     *
     * @throws InvalidResourceType
     * @return self
     */
    public function setResource($resource)
    {
        if ($resource instanceof Weave) {
            $resource = $resource->___getObject();
        }
        if ($resource instanceof ResourceObject === false && $resource instanceof Weave === false) {
            $type = is_object($resource) ? get_class($resource) : gettype($resource);
            throw new InvalidResourceType($type);
        }
        $this->resource = $resource;
        return $this;
    }
    /**
     * Set Exception
     *
     * @param \Exception $e
     * @param int        $exceptionId
     *
     * @return self
     */
    public function setException(Exception $e, $exceptionId)
    {
        $this->e = $e;
        $this->code = $e->getCode();
        $this->headers = array();
        $this->body = $exceptionId;
        return $this;
    }
    /**
     * Render
     *
     * @param Callable $renderer
     *
     * @return self
     */
    public function render(callable $renderer = null)
    {
        if (is_callable($renderer)) {
            $this->view = $renderer($this->body);
        } else {
            $this->view = (string) $this->resource;
        }
        return $this;
    }
    /**
     * Make response object with RFC 2616 compliant HTTP header
     *
     * @return self
     * @deprecated
     */
    public function prepare()
    {
        trigger_error('unnecessary science 0.6.0', E_USER_DEPRECATED);
        return $this;
    }
    /**
     * Transfer representational state to http client (or console output)
     *
     * @return ResponseInterface
     */
    public function send()
    {
        $this->response = new Response($this->view, $this->resource->code, (array) $this->resource->headers);
        // compliant with RFC 2616.
        $this->response;
        if (PHP_SAPI === 'cli') {
            if ($this->resource instanceof Page) {
                $this->resource->headers = $this->response->headers->all();
            }
            $statusText = Response::$statusTexts[$this->resource->code];
            $this->consoleOutput->send($this->resource, $statusText, ConsoleOutput::MODE_REQUEST);
        } else {
            $this->response->send();
        }
        return $this;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension\ConsoleOutput;

use BEAR\Sunday\Extension\ExtensionInterface;
/**
 * Interface for console output
 *
 * @package    BEAR.Sunday
 */
interface ConsoleOutputInterface extends ExtensionInterface
{
    
}
namespace Guzzle\Log;

/**
 * Adapter class that allows Guzzle to log data to various logging implementations.
 */
interface LogAdapterInterface
{
    /**
     * Log a message at a priority
     *
     * @param string  $message  Message to log
     * @param integer $priority Priority of message (use the \LOG_* constants of 0 - 7)
     * @param mixed   $extras   Extra information to log in event
     */
    public function log($message, $priority = LOG_INFO, $extras = null);
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger;

use BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface;
use BEAR\Resource\LoggerInterface as ResourceLoggerInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Resource\Logger as ResourceLogger;
use Ray\Di\Di\Inject;
/**
 * Logger
 *
 * @package BEAR.Package
 */
final class ApplicationLogger implements ApplicationLoggerInterface
{
    /**
     * Resource logs
     *
     * @var ResourceLoggerInterface
     */
    private $logger;
    /**
     * @param ResourceLoggerInterface $logger
     *
     * @Inject
     */
    public function __construct(ResourceLoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * {@inheritdoc}
     */
    public function register(AppInterface $app)
    {
        register_shutdown_function(function () {
            $this->logger->write();
        });
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ConsoleOutput;

use BEAR\Sunday\Extension\ConsoleOutput\ConsoleOutputInterface;
use BEAR\Resource\AbstractObject as ResourceObject;
use Guzzle\Parser\UriTemplate\UriTemplate;
/**
 * Cli Output
 *
 * @package    BEAR.Sunday
 * @subpackage Web
 */
final class ConsoleOutput implements ConsoleOutputInterface
{
    const MODE_REQUEST = 'request';
    const MODE_VIEW = 'view';
    const MODE_VALUE = 'value';
    /**
     * Send CLI output
     *
     * @param ResourceObject $resource
     * @param string         $statusText
     * @param string         $mode
     */
    public function send(ResourceObject $resource, $statusText = '', $mode = self::MODE_VIEW)
    {
        $label = '[1;32m';
        $label1 = '[1;33m';
        $label2 = '[4;30m';
        $close = '[0m';
        // code
        $codeMsg = $label . $resource->code . ' ' . $statusText . $close . PHP_EOL;
        echo $codeMsg;
        // resource headers
        foreach ($resource->headers as $name => $value) {
            $value = is_array($value) ? json_encode($value, true) : $value;
            echo "{$label1}{$name}: {$close}{$value}" . PHP_EOL;
        }
        // body
        echo "{$label}[BODY]{$close}" . PHP_EOL;
        if ($resource->view) {
            echo $resource->view;
            goto complete;
        }
        $isTraversable = is_array($resource->body) || $resource->body instanceof \Traversable;
        if (!$isTraversable) {
            $resource->body;
            goto complete;
        }
        foreach ($resource->body as $key => $body) {
            if ($body instanceof \BEAR\Resource\Request) {
                switch ($mode) {
                    case self::MODE_REQUEST:
                        $body = "{$label2}" . $body->toUri() . $close;
                        break;
                    case self::MODE_VALUE:
                        $value = $body();
                        $body = var_export($value, true) . " {$label2}" . $body->toUri() . $close;
                        break;
                    case self::MODE_VIEW:
                    default:
                        $body = (string) $body . " {$label2}" . $body->toUri() . $close;
                        break;
                }
            }
            $body = is_array($body) ? var_export($body, true) : $body;
            echo "{$label1}{$key}{$close}:" . $body . PHP_EOL;
        }
        // @codingStandardsIgnoreStart
        complete:
        // @codingStandardsIgnoreEnd
        // links
        echo PHP_EOL;
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Log
 */
namespace Zend\Log\Writer;

use Traversable;
use Zend\Log\Exception;
use Zend\Log\Formatter\Simple as SimpleFormatter;
use Zend\Stdlib\ErrorHandler;
/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Writer
 */
class Stream extends AbstractWriter
{
    /**
     * Separator between log entries
     *
     * @var string
     */
    protected $logSeparator = PHP_EOL;
    /**
     * Holds the PHP stream to log to.
     *
     * @var null|stream
     */
    protected $stream = null;
    /**
     * Constructor
     *
     * @param  string|resource|array|Traversable $streamOrUrl Stream or URL to open as a stream
     * @param  string|null $mode Mode, only applicable if a URL is given
     * @param  null|string $logSeparator Log separator string
     * @return Stream
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function __construct($streamOrUrl, $mode = null, $logSeparator = null)
    {
        if ($streamOrUrl instanceof Traversable) {
            $streamOrUrl = iterator_to_array($streamOrUrl);
        }
        if (is_array($streamOrUrl)) {
            $mode = isset($streamOrUrl['mode']) ? $streamOrUrl['mode'] : null;
            $logSeparator = isset($streamOrUrl['log_separator']) ? $streamOrUrl['log_separator'] : null;
            $streamOrUrl = isset($streamOrUrl['stream']) ? $streamOrUrl['stream'] : null;
        }
        // Setting the default mode
        if (null === $mode) {
            $mode = 'a';
        }
        if (is_resource($streamOrUrl)) {
            if ('stream' != get_resource_type($streamOrUrl)) {
                throw new Exception\InvalidArgumentException(sprintf('Resource is not a stream; received "%s', get_resource_type($streamOrUrl)));
            }
            if ('a' != $mode) {
                throw new Exception\InvalidArgumentException(sprintf('Mode must be "a" on existing streams; received "%s"', $mode));
            }
            $this->stream = $streamOrUrl;
        } else {
            ErrorHandler::start();
            $this->stream = fopen($streamOrUrl, $mode, false);
            $error = ErrorHandler::stop();
            if (!$this->stream) {
                throw new Exception\RuntimeException(sprintf('"%s" cannot be opened with mode "%s"', $streamOrUrl, $mode), 0, $error);
            }
        }
        if (null !== $logSeparator) {
            $this->setLogSeparator($logSeparator);
        }
        $this->formatter = new SimpleFormatter();
    }
    /**
     * Write a message to the log.
     *
     * @param array $event event data
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function doWrite(array $event)
    {
        $line = $this->formatter->format($event) . $this->logSeparator;
        fwrite($this->stream, $line);
    }
    /**
     * Set log separator string
     *
     * @param  string $logSeparator
     * @return Stream
     */
    public function setLogSeparator($logSeparator)
    {
        $this->logSeparator = (string) $logSeparator;
        return $this;
    }
    /**
     * Get log separator string
     *
     * @return string
     */
    public function getLogSeparator()
    {
        return $this->logSeparator;
    }
    /**
     * Close the stream resource.
     *
     * @return void
     */
    public function shutdown()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Stdlib
 */
namespace Zend\Stdlib;

use ErrorException;
/**
 * ErrorHandler that can be used to catch internal PHP errors
 * and convert to a ErrorException instance.
 *
 * @category   Zend
 * @package    Zend_Stdlib
 */
abstract class ErrorHandler
{
    /**
     * Flag to mark started
     *
     * @var bool
     */
    protected static $started = false;
    /**
     * All errors as one instance of ErrorException
     * using the previous exception support.
     *
     * @var null|ErrorException
     */
    protected static $errorException = null;
    /**
     * If the error handler has been started.
     *
     * @return bool
     */
    public static function started()
    {
        return static::$started;
    }
    /**
     * Starting the error handler
     *
     * @param int $errorLevel
     * @throws Exception\LogicException If already started
     */
    public static function start($errorLevel = \E_WARNING)
    {
        if (static::started() === true) {
            throw new Exception\LogicException('ErrorHandler already started');
        }
        static::$started = true;
        static::$errorException = null;
        set_error_handler(array(get_called_class(), 'addError'), $errorLevel);
    }
    /**
     * Stopping the error handler
     *
     * @param  bool $throw Throw the ErrorException if any
     * @return null|ErrorException
     * @throws Exception\LogicException If not started before
     * @throws ErrorException If an error has been catched and $throw is true
     */
    public static function stop($throw = false)
    {
        if (static::started() === false) {
            throw new Exception\LogicException('ErrorHandler not started');
        }
        $errorException = static::$errorException;
        static::$started = false;
        static::$errorException = null;
        restore_error_handler();
        if ($errorException && $throw) {
            throw $errorException;
        }
        return $errorException;
    }
    /**
     * Add an error to the stack.
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @return void
     */
    public static function addError($errno, $errstr = '', $errfile = '', $errline = 0)
    {
        static::$errorException = new ErrorException($errstr, 0, $errno, $errfile, $errline, static::$errorException);
    }
}
namespace Guzzle\Log;

/**
 * Adapter class that allows Guzzle to log data using various logging implementations
 */
abstract class AbstractLogAdapter implements LogAdapterInterface
{
    protected $log;
    /**
     * {@inheritdoc}
     */
    public function getLogObject()
    {
        return $this->log;
    }
}
namespace Guzzle\Log;

use Zend\Log\Logger;
/**
 * Adapts a Zend Framework 2 logger object
 */
class Zf2LogAdapter extends AbstractLogAdapter
{
    /**
     * {@inheritdoc}
     */
    public function __construct(Logger $logObject)
    {
        $this->log = $logObject;
    }
    /**
     * {@inheritdoc}
     */
    public function log($message, $priority = LOG_INFO, $extras = null)
    {
        $this->log->log($priority, $message, $extras ?: array());
    }
}
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for render view
 *
 * @package BEAR.Resource
 */
interface RenderInterface
{
    /**
     * Render
     *
     * @param AbstractObject $resourceObject
     *
     * @return self
     */
    public function render(AbstractObject $resourceObject);
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Debug\ExceptionHandle;

use BEAR\Resource\AbstractObject as AbstractPage;
/**
 * Error page
 *
 * @package    BEAR.Sunday
 * @subpackage Page
 */
final class ErrorPage extends AbstractPage
{
    /**
     * Code
     *
     * @var int
     */
    public $code = 500;
    /**
     * Headers
     *
     * @var array
     */
    public $headers = array();
    /**
     * Body
     *
     * @var mixed
     */
    public $body = '';
    /**
     * Constructor
     */
    public function __construct()
    {
        
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension\ResourceView;

use BEAR\Resource\RenderInterface;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
/**
 * Interface for console output
 *
 * @package    BEAR.Sunday
 */
interface TemplateEngineRendererInterface extends RenderInterface
{
    /**
     * ViewRenderer Setter
     *
     * @param TemplateEngineAdapterInterface $templateEngineAdapter
     *
     * @Inject
     */
    public function __construct(TemplateEngineAdapterInterface $templateEngineAdapter);
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use BEAR\Sunday\Extension\ResourceView\TemplateEngineRendererInterface;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use BEAR\Resource\AbstractObject;
use Ray\Aop\Weave;
use ReflectionClass;
use Ray\Di\Di\Inject;
/**
 * Request renderer
 *
 * @package    BEAR.Sunday
 * @subpackage View
 */
class TemplateEngineRenderer implements TemplateEngineRendererInterface
{
    /**
     * Template engine adapter
     *
     * @var TemplateEngineAdapterInterface
     */
    private $templateEngineAdapter;
    /**
     * ViewRenderer Setter
     *
     * @param TemplateEngineAdapterInterface $templateEngineAdapter
     *
     * @Inject
     * @SuppressWarnings("long")
     */
    public function __construct(TemplateEngineAdapterInterface $templateEngineAdapter)
    {
        $this->templateEngineAdapter = $templateEngineAdapter;
    }
    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.RenderInterface::render()
     * @SuppressWarnings("long")
     */
    public function render(AbstractObject $resourceObject)
    {
        if (is_scalar($resourceObject->body)) {
            $resourceObject->view = $resourceObject->body;
            return (string) $resourceObject->body;
        }
        $class = $resourceObject instanceof Weave ? get_class($resourceObject->___getObject()) : get_class($resourceObject);
        $file = (new ReflectionClass($class))->getFileName();
        // assign 'resource'
        $this->templateEngineAdapter->assign('resource', $resourceObject);
        // assign all
        if (is_array($resourceObject->body) || $resourceObject->body instanceof \Traversable) {
            $this->templateEngineAdapter->assignAll((array) $resourceObject->body);
        }
        $templateFileWithoutExtension = substr($file, 0, -3);
        $resourceObject->view = $this->templateEngineAdapter->fetch($templateFileWithoutExtension);
        return $resourceObject->view;
    }
}
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension\TemplateEngine;

use BEAR\Sunday\Extension\ExtensionInterface;
/**
 * Interface for template engine adapter
 *
 * @package    BEAR.Sunday
 * @subpackage Resource
 */
interface TemplateEngineAdapterInterface extends ExtensionInterface
{
    /**
     * Assigns a variable
     *
     * @param string $tplVar the template variable name(s)
     * @param mixed  $value  the value to assign
     *
     * @return self
     */
    public function assign($tplVar, $value);
    /**
     * Assigns all variables
     *
     * @param array $values
     *
     * @return self
     */
    public function assignAll(array $values);
    /**
     * Fetches a rendered template
     *
     * @param string $template the resource handle of the template file or template object
     *
     * @return string rendered template output
     */
    public function fetch($template);
    /**
     * Return template full path.
     *
     * @return string
     */
    public function getTemplateFile();
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

use Smarty;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use BEAR\Sunday\Exception\TemplateNotFound;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;
/**
 * Smarty adapter
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class SmartyAdapter implements TemplateEngineAdapterInterface
{
    /**
     * smarty
     *
     * @var Smarty
     */
    private $smarty;
    /**
     * Template file
     *
     * @var string
     */
    private $template;
    /**
     * @var bool
     */
    private $isProd;
    /**
     * File extension
     *
     * @var string
     */
    const EXT = 'tpl';
    /**
     * Is production ?
     *
     * @param bool $isProd
     *
     * @Inject
     * @Named("is_prod")
     */
    public function setIsProd($isProd)
    {
        $this->isProd = $isProd;
    }
    /**
     * Constructor
     *
     * Smarty $smarty
     *
     * @Inject
     */
    public function __construct(Smarty $smarty)
    {
        $this->smarty = $smarty;
    }
    /**
     * Init
     *
     * @PostConstruct
     */
    public function init()
    {
        if ($this->isProd) {
            $this->smarty->force_compile = false;
            $this->smarty->compile_check = false;
        }
    }
    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\Resource\View.TemplateEngineAdapter::assign()
     */
    public function assign($tplVar, $value)
    {
        $this->smarty->assign($tplVar, $value);
    }
    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\Resource\View.TemplateEngineAdapter::assignAll()
     */
    public function assignAll(array $values)
    {
        $this->smarty->assign($values);
    }
    /**
     * (non-PHPdoc)
     * @see BEAR\Sunday\View.Render::fetch()
     */
    public function fetch($tplWithoutExtension)
    {
        $this->template = $tplWithoutExtension . self::EXT;
        $this->fileExists($this->template);
        return $this->smarty->fetch($this->template);
    }
    /**
     * Return file exists
     *
     * @param string $template
     *
     * @throws TemplateNotFound
     */
    private function fileExists($template)
    {
        if (!file_exists($template)) {
            throw new TemplateNotFound($template);
        }
    }
    /**
     * Return template full path.
     *
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->template;
    }
}
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Aura\Router\Map;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Exception\MethodNotAllowed;
use Ray\Di\Di\Inject;
/**
 * Standard min router
 *
 * The constructor can accepts "Aura.Route" routing
 * @see https://github.com/auraphp/Aura.Router
 *
 * @package    BEAR.Package
 * @subpackage Route
 */
final class MinRouter implements RouterInterface
{
    /**
     * $GLOBALS
     *
     * @var array
     */
    private $globals;
    /**
     * map
     *
     * @var \Aura\Router\Map;
     */
    private $map;
    const METHOD_OVERRIDE = 'X-HTTP-Method-Override';
    const METHOD_OVERRIDE_GET = '_method';
    /**
     * Constructor
     *
     * @param Map $map
     * @Inject(optional=true)
     */
    public function __construct(Map $map = null)
    {
        $this->map = $map;
    }
    /**
     * {@inheritDoc}
     */
    public function setGlobals($global)
    {
        $this->globals = $global;
        return $this;
    }
    /**
     * {@inheritDoc}
     *
     * @throws BadRequest
     * @throws MethodNotAllowed
     */
    public function setArgv($argv)
    {
        if (count($argv) < 3) {
            throw new BadRequest('Usage: [get|post|put|delete] [uri]');
        }
        $isMethodAllowed = in_array($argv[1], array('get', 'post', 'put', 'delete', 'options'));
        if (!$isMethodAllowed) {
            throw new MethodNotAllowed($argv[1]);
        }
        $globals['_SERVER']['REQUEST_METHOD'] = $argv[1];
        $globals['_SERVER']['REQUEST_URI'] = parse_url($argv[2], PHP_URL_PATH);
        parse_str(parse_url($argv[2], PHP_URL_QUERY), $get);
        $globals['_GET'] = $get;
        $this->globals = $globals;
        return $this;
    }
    /**
     * {@inheritDoc}
     *
     * @return array [$method, $pageUri, $query]
     */
    public function match()
    {
        $this->globals = $this->globals ?: $GLOBALS;
        $globals = $this->globals;
        $uri = $globals['_SERVER']['REQUEST_URI'];
        $route = $this->map ? $this->map->match(parse_url($uri, PHP_URL_PATH), $globals['_SERVER']) : false;
        if ($route === false) {
            list($method, $query, ) = $this->getMethodQuery();
            $pageUri = $this->getPageKey();
        } else {
            $method = $route->values['action'];
            $pageUri = $route->values['page'];
            $query = array();
            $keys = array_keys($route->params);
            foreach ($keys as $key) {
                $query[$key] = $route->values[$key];
            }
        }
        unset($query[self::METHOD_OVERRIDE]);
        return array($method, $pageUri, $query);
    }
    /**
     * Return request method
     *
     * @return array [$method, $query]
     */
    private function getMethodQuery()
    {
        $globals = $this->globals;
        if ($globals['_SERVER']['REQUEST_METHOD'] === 'GET' && isset($globals['_GET'][self::METHOD_OVERRIDE_GET])) {
            $method = $globals['_GET'][self::METHOD_OVERRIDE_GET];
            $query = $globals['_GET'];
        } elseif ($globals['_SERVER']['REQUEST_METHOD'] === 'POST' && isset($globals['_POST'][self::METHOD_OVERRIDE])) {
            $method = $globals['_POST'][self::METHOD_OVERRIDE];
            $query = $globals['_POST'];
        } else {
            $method = $globals['_SERVER']['REQUEST_METHOD'];
            $query = $globals['_GET'];
        }
        $method = strtolower($method);
        return array($method, $query);
    }
    /**
     * Return page key
     *
     * @return array [$method, $pagekey]
     * @throws \InvalidArgumentException
     */
    private function getPageKey()
    {
        if (!isset($this->globals['_SERVER']['REQUEST_URI'])) {
            return '404';
        }
        $pageKey = substr($this->globals['_SERVER']['REQUEST_URI'], 1);
        return $pageKey;
    }
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Router
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Router;

/**
 * 
 * A collection point for URI routes.
 * 
 * @package Aura.Router
 * 
 */
class Map
{
    /**
     * 
     * Currently processing this attached common route information.
     * 
     * @var array
     * 
     */
    protected $attach_common = null;
    /**
     * 
     * Currently processing these attached routes.
     * 
     * @var array
     * 
     */
    protected $attach_routes = null;
    /**
     * 
     * Route definitions; these will be converted into objects.
     * 
     * @var array
     * 
     */
    protected $definitions = array();
    /**
     * 
     * A RouteFactory for creating route objects.
     * 
     * @var RouteFactory
     * 
     */
    protected $route_factory;
    /**
     * 
     * Route objects created from the definitons.
     * 
     * @var array
     * 
     */
    protected $routes = array();
    /**
     * 
     * Logging information about which routes were attempted to match.
     * 
     * @var array
     * 
     */
    protected $log = array();
    /**
     * 
     * Constructor.
     * 
     * @param DefinitionFactory $definition_factory A factory for creating 
     * definition objects.
     * 
     * @param RouteFactory $route_factory A factory for creating route 
     * objects.
     * 
     * @param array $attach A series of route definitions to be attached to
     * the router.
     * 
     */
    public function __construct(DefinitionFactory $definition_factory, RouteFactory $route_factory, array $attach = null)
    {
        $this->definition_factory = $definition_factory;
        $this->route_factory = $route_factory;
        foreach ((array) $attach as $path_prefix => $spec) {
            $this->attach($path_prefix, $spec);
        }
    }
    /**
     * 
     * Adds a single route definition to the stack.
     * 
     * @param string $name The route name for `generate()` lookups.
     * 
     * @param string $path The route path.
     * 
     * @param array $spec The rest of the route definition, with keys for
     * `params`, `values`, etc.
     * 
     * @return void
     * 
     */
    public function add($name, $path, array $spec = null)
    {
        $spec = (array) $spec;
        // overwrite the name and path
        $spec['name'] = $name;
        $spec['path'] = $path;
        // these should be set only by the map
        unset($spec['name_prefix']);
        unset($spec['path_prefix']);
        // append to the route definitions
        $this->definitions[] = $this->definition_factory->newInstance('single', $spec);
    }
    /**
     * 
     * Attaches several routes at once to a specific path prefix.
     * 
     * @param string $path_prefix The path that the routes should be attached
     * to.
     * 
     * @param array $spec An array of common route information, with an
     * additional `routes` key to define the routes themselves.
     * 
     * @return void
     * 
     */
    public function attach($path_prefix, $spec)
    {
        $this->definitions[] = $this->definition_factory->newInstance('attach', $spec, $path_prefix);
    }
    /**
     * 
     * Gets a route that matches a given path and other server conditions.
     * 
     * @param string $path The path to match against.
     * 
     * @param array $server An array copy of $_SERVER.
     * 
     * @return Route|false Returns a Route object when it finds a match, or 
     * boolean false if there is no match.
     * 
     */
    public function match($path, array $server = null)
    {
        // reset the log
        $this->log = array();
        // look through existing route objects
        foreach ($this->routes as $route) {
            $this->logRoute($route);
            if ($route->isMatch($path, $server)) {
                return $route;
            }
        }
        // convert remaining definitions as needed
        while ($this->attach_routes || $this->definitions) {
            $route = $this->createNextRoute();
            $this->logRoute($route);
            if ($route->isMatch($path, $server)) {
                return $route;
            }
        }
        // no joy
        return false;
    }
    /**
     * 
     * Looks up a route by name, and interpolates data into it to return
     * a URI path.
     * 
     * @param string $name The route name to look up.
     * 
     * @param array $data The data to inpterolate into the URI; data keys
     * map to param tokens in the path.
     * 
     * @return string|false A URI path string if the route name is found, or
     * boolean false if not.
     * 
     */
    public function generate($name, $data = null)
    {
        // do we already have the route object?
        if (isset($this->routes[$name])) {
            return $this->routes[$name]->generate($data);
        }
        // convert remaining definitions as needed
        while ($this->attach_routes || $this->definitions) {
            $route = $this->createNextRoute();
            if ($route->name == $name) {
                return $route->generate($data);
            }
        }
        // no joy
        return false;
    }
    /**
     * 
     * Reset the map to use an array of Route objects.
     * 
     * @param array $routes Use this array of route objects, likely generated
     * from `getRoutes()`.
     * 
     * @return void
     * 
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
        $this->definitions = array();
        $this->attach_custom = array();
        $this->attach_routes = array();
    }
    /**
     * 
     * Get the array of Route objects in this map, likely for caching and
     * re-setting via `setRoutes()`.
     * 
     * @return array
     * 
     */
    public function getRoutes()
    {
        // convert remaining definitions as needed
        while ($this->attach_routes || $this->definitions) {
            $this->createNextRoute();
        }
        return $this->routes;
    }
    /**
     * 
     * Get the log of attempted route matches.
     * 
     * @return array
     * 
     */
    public function getLog()
    {
        return $this->log;
    }
    /**
     * 
     * Add a route to the log of attempted matches.
     * 
     * @param Route $route Route object
     * 
     * @return array
     * 
     */
    protected function logRoute(Route $route)
    {
        $this->log[] = $route;
    }
    /**
     * 
     * Gets the next Route object in the stack, converting definitions to 
     * Route objects as needed.
     * 
     * @return Route|false A Route object, or boolean false at the end of the 
     * stack.
     * 
     */
    protected function createNextRoute()
    {
        // do we have attached routes left to process?
        if ($this->attach_routes) {
            // yes, get the next attached definition
            $spec = $this->getNextAttach();
        } else {
            // no, get the next unattached definition
            $spec = $this->getNextDefinition();
        }
        // create a route object from it
        $route = $this->route_factory->newInstance($spec);
        // retain the route object ...
        $name = $route->name;
        if ($name) {
            // ... under its name so we can look it up later
            $this->routes[$name] = $route;
        } else {
            // ... under no name, which means we can't look it up later
            $this->routes[] = $route;
        }
        // return whatever route got retained
        return $route;
    }
    /**
     * 
     * Gets the next route definition from the stack.
     * 
     * @return array A route definition.
     * 
     */
    protected function getNextDefinition()
    {
        // get the next definition and extract the definition type
        $def = array_shift($this->definitions);
        $spec = $def->getSpec();
        $type = $def->getType();
        // is it a 'single' definition type?
        if ($type == 'single') {
            // done!
            return $spec;
        }
        // it's an 'attach' definition; set up for attach processing.
        // retain the routes from the array ...
        $this->attach_routes = $spec['routes'];
        unset($spec['routes']);
        // ... and the remaining common information
        $this->attach_common = $spec;
        // reset the internal pointer of the array to avoid misnamed routes
        reset($this->attach_routes);
        // now get the next attached route
        return $this->getNextAttach();
    }
    /**
     * 
     * Gets the next attached route definition.
     * 
     * @return array A route definition.
     * 
     */
    protected function getNextAttach()
    {
        $key = key($this->attach_routes);
        $val = array_shift($this->attach_routes);
        // which definition form are we using?
        if (is_string($key) && is_string($val)) {
            // short form, named in key
            $spec = array('name' => $key, 'path' => $val, 'values' => array('action' => $key));
        } elseif (is_int($key) && is_string($val)) {
            // short form, no name
            $spec = array('path' => $val);
        } elseif (is_string($key) && is_array($val)) {
            // long form, named in key
            $spec = $val;
            $spec['name'] = $key;
            // if no action, use key
            if (!isset($spec['values']['action'])) {
                $spec['values']['action'] = $key;
            }
        } elseif (is_int($key) && is_array($val)) {
            // long form, no name
            $spec = $val;
        } else {
            throw new Exception("Route spec for '{$key}' should be a string or array.");
        }
        // unset any path or name prefix on the spec itself
        unset($spec['name_prefix']);
        unset($spec['path_prefix']);
        // now merge with the attach info
        $spec = array_merge_recursive($this->attach_common, $spec);
        // done!
        return $spec;
    }
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Router
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Router;

/**
 * 
 * A factory to create Definition objects.
 * 
 * @package Aura.Router
 * 
 */
class DefinitionFactory
{
    /**
     * 
     * Returns a new Definition instance.
     * 
     * @param string $type The type of definition, 'single' or 'attach'.
     * 
     * @param array|callable $spec The definition spec: either an array, or a
     * callable that returns an array.
     * 
     * @param string $path_prefix For 'attach' definitions, use this as the 
     * prefix for attached paths.
     * 
     * @return Route
     * 
     */
    public function newInstance($type, $spec, $path_prefix = null)
    {
        return new Definition($type, $spec, $path_prefix);
    }
}
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Router
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Router;

/**
 * 
 * A factory to create Route objects.
 * 
 * @package Aura.Router
 * 
 */
class RouteFactory
{
    /**
     * 
     * An array of default parameters for Route objects.
     * 
     * @var array
     * 
     */
    protected $params = array('name' => null, 'path' => null, 'params' => null, 'values' => null, 'method' => null, 'secure' => null, 'routable' => true, 'is_match' => null, 'generate' => null, 'name_prefix' => null, 'path_prefix' => null);
    /**
     * 
     * Returns a new Route instance.
     * 
     * @param array $params An array of key-value pairs corresponding to the
     * Route parameters.
     * 
     * @return Route
     * 
     */
    public function newInstance(array $params)
    {
        $params = array_merge($this->params, $params);
        return new Route($params['name'], $params['path'], $params['params'], $params['values'], $params['method'], $params['secure'], $params['routable'], $params['is_match'], $params['generate'], $params['name_prefix'], $params['path_prefix']);
    }
}
