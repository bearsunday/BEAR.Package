<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\DevInvoker;
use BEAR\Resource\Request;
use BEAR\Sunday\Extension\ResourceView\TemplateEngineRendererInterface;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use BEAR\Package\Module\Cache\Interceptor\CacheLoader;
use DateInterval;
use DateTime;
use ReflectionClass;
use ReflectionObject;
use Traversable;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Aop\WeavedInterface;

/**
 * Request renderer
 *
 * @SuppressWarnings(PHPMD)
 */
class DevTemplateEngineRenderer implements TemplateEngineRendererInterface
{
    const NO_CACHE = 'label-default';
    const WRITE_CACHE = 'label-danger';
    const READ_CACHE = 'label-success';
    const BADGE_ARGS = '<span class="badge badge-info">Arguments</span>';
    const BADGE_CACHE = '<span class="badge badge-info">Cache</span>';
    const BADGE_INTERCEPTORS = '<span class="badge badge-info">Interceptors</span>';
    const BADGE_PROFILE = '<span class="badge badge-info">Profile</span>';
    const ICON_LIFE = '<span class="glyphicon glyphicon-refresh"></span>';
    const ICON_TIME = '<span class="glyphicon glyphicon-time"></span>';
    const ICON_NA = '<span class="glyphicon glyphicon-ban-circle"></span>';
    const DIV_WELL = '<div style="padding:10px;">';

    /**
     * Template engine adapter
     *
     * @var TemplateEngineAdapterInterface
     */
    private $templateEngineAdapter;

    /**
     * BEAR.Package dir
     *
     * @var string
     */
    private $packageDir;

    /**
     * BEAR.Sunday dir
     *
     * @var string
     */
    private $sundayDir;

    /**
     * ViewRenderer Setter
     *
     * @param TemplateEngineAdapterInterface $templateEngineAdapter
     *
     * @Inject
     */
    public function __construct(TemplateEngineAdapterInterface $templateEngineAdapter)
    {
        $this->templateEngineAdapter = $templateEngineAdapter;
    }

    /**
     * Set packageDir
     *
     * @param string $packageDir
     *
     * @Inject
     * @Named("package_dir")
     */
    public function setPackageDir($packageDir)
    {
        $this->packageDir = $packageDir;
    }

    /**
     * Set sunday_dir
     *
     * @param string $sundayDir
     *
     * @Inject
     * @Named("sunday_dir")
     */
    public function setSundayDir($sundayDir)
    {
        $this->sundayDir = $sundayDir;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $resourceObject)
    {
        try {
            return $this->templateRender($resourceObject);
        } catch (\Exception $e) {
            return $this->outputErrorToString($e);
        }
    }

    /**
     * @param \Exception $e
     *
     * @return int
     */
    private function outputErrorToString(\Exception $e)
    {
        error_log($e);

        $previous = $e->getPrevious();
        $previousMsg = $previous ? sprintf("<strong>%s</strong> in file %s on %s",
            $previous->getMessage(),
            $previous->getFile(),
            $previous->getLine()
        ) : '';
        return printf('<h2>%s</h2> in file %s on line %s<h3>%s</h3><pre class="error_in_to_string">%s</pre>',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $previousMsg,
            $e->getTraceAsString()
        );
    }

    /**
     * @param ResourceObject $resourceObject
     *
     * @return $this|bool|float|int|string
     */
    private function templateRender(ResourceObject $resourceObject)
    {
        if (is_scalar($resourceObject->body)) {
            $resourceObject->view = $resourceObject->body;
            return $resourceObject->body;
        }
        if (PHP_SAPI === 'cli') {
            // delegate original method to avoid render dev html.
            return (new TemplateEngineRenderer($this->templateEngineAdapter))->render($resourceObject);
        }
        // resource code editor
        if ($resourceObject instanceof WeavedInterface) {
            $pageFile = (new ReflectionClass($resourceObject))->getParentClass()->getFileName();
        } else {
            $pageFile = (new ReflectionClass($resourceObject))->getFileName();
        }

        // resource template editor
        $dir = pathinfo($pageFile, PATHINFO_DIRNAME);
        $this->templateEngineAdapter->assign('resource', $resourceObject);
        if (is_array($resourceObject->body) || $resourceObject->body instanceof Traversable) {
            $this->templateEngineAdapter->assignAll($resourceObject->body);
        }
        $templateFileBase = $dir . DIRECTORY_SEPARATOR . substr(
            basename($pageFile),
            0,
            strlen(basename($pageFile)) - 3
        );

        // add tool bar
        $resourceObject->view = $body = $this->templateEngineAdapter->fetch($templateFileBase);
        $body = $this->addJsDevToolLadingHtml($body);
        $templateFile = $this->templateEngineAdapter->getTemplateFile();
        $templateFile = $this->makeRelativePath($templateFile);
        $label = $this->getLabel($body, $resourceObject, $templateFile);

        return $label;
    }

    /**
     * Return JS install html for dev tool
     *
     * @param string $body
     *
     * @return string
     */
    private function addJsDevToolLadingHtml($body)
    {
        if (strpos($body, '</body>') === false) {
            return $body;
        }
        $bootstrapCss = '<link href="//koriym.github.io/BEAR.Package/assets/css/bootstrap.bear.css" rel="stylesheet"><link href="//koriym.github.io/BEAR.Package/assets/css/bear.dev.css" rel="stylesheet">';
        $bootstrapCss .= strpos($body, 'glyphicons.css') ? '' : '<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">';
        $tabJs = strpos($body, '/assets/js/bootstrap-tab.js') ? '' : '<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/js/bootstrap-tab.js"></script>';
        $bootstrapJs = '<link href="//netdna.bootstrapcdn.com/bootswatch/3.0.0/united/bootstrap.min.css" rel="stylesheet">';
        $toolLoad = <<<EOT
<!-- BEAR.Sunday dev tool load -->
<script src="//www.google.com/jsapi"></script>
<script>if (typeof jQuery == "undefined") {google.load("jquery", "1.7.1");}</script>{$bootstrapCss}{$tabJs}

<!-- /BEAR.Sunday dev tool load -->
EOT;
        $toolLoad = str_replace(["\n", "  "], '', $toolLoad);
        $body = str_replace('<head>', "<head>\n{$toolLoad}", $body);
        // $body = $body .  $toolLoad;
        return $body;
    }

    /**
     * Get relative path from system root.
     *
     * @param string $file
     *
     * @return mixed
     * @return string
     */
    private function makeRelativePath($file)
    {
        $file = str_replace($this->packageDir, '', $file);
        $file = str_replace($this->sundayDir, '/vendor/bear/sunday', $file);
        return $file;
    }

    /**
     * Return label
     *
     * @param                $body
     * @param ResourceObject $resourceObject
     * @param                $templateFile
     *
     * @return string
     */
    private function getLabel($body, ResourceObject $resourceObject, $templateFile)
    {
        $cache = isset($resourceObject->headers[CacheLoader::HEADER_CACHE]) ? json_decode($resourceObject->headers[CacheLoader::HEADER_CACHE], true) : false;
        if ($cache === false) {
            $labelColor = self::NO_CACHE;
        } elseif (isset($cache['mode']) && $cache['mode'] === 'W') {
            $labelColor = self::WRITE_CACHE;
        } else {
            $labelColor = self::READ_CACHE;
        }

        // var
        $result = $this->addResourceMetaInfo($resourceObject, $labelColor, $templateFile, $body);

        return $result;
    }

    /**
     * @param ResourceObject $resourceObject
     * @param                $labelColor
     * @param                $templateFile
     * @param                $body
     *
     * @return string
     */
    private function addResourceMetaInfo(ResourceObject $resourceObject, $labelColor, $templateFile, $body)
    {
        $resourceName = ($resourceObject->uri ? : get_class($resourceObject));
        // code editor
        $ref = new ReflectionObject($resourceObject);
        $codeFile = ($resourceObject instanceof WeavedInterface) ? $ref->getParentClass()->getFileName(): $ref->getFileName();
        $codeFile = $this->makeRelativePath($codeFile);
        $var = $this->getVar($resourceObject->body);
        $resourceKey = spl_object_hash($resourceObject);
        $bodyIntTool = preg_replace('/<!-- BEAR\.Sunday dev tool load -->.*BEAR\.Sunday dev tool load -->/', '', $body);

        $resourceBody = preg_replace_callback(
            '/<!-- resource(.*?)resource_tab_end -->/s',
            function ($matches) {
                $uri = substr(explode(' ', $matches[1])[0], 1);
                preg_match('/ <!-- resource_body_start -->(.*?)<!-- resource_body_end -->/s', $matches[1], $resourceBodyMatch);
                return "<!-- resource:$uri -->{$resourceBodyMatch[1]}<!-- /resource:$uri -->";
            },
            $bodyIntTool
        );
        $resourceBodyHtml = highlight_string($resourceBody, true);
        $info = $this->getResourceInfo($resourceObject);
        $rmReturn = function ($str) {
            return str_replace("\n", '', $str);
        };
        $result = <<<EOT
<!-- resource:{$resourceName} -->
<div class="bearsunday">
<div class="toolbar">
    <span class="label {$labelColor}">{$resourceName}</span>
    <a data-toggle="tab" href="#{$resourceKey}_body" class="home"><span class="glyphicon glyphicon-home"
    rel="tooltip" title="Home"></span></a>
    <a data-toggle="tab" href="#{$resourceKey}_var"><span class="glyphicon glyphicon-zoom-in" rel="tooltip"
    title="Status"></span></a>
    <a data-toggle="tab" href="#{$resourceKey}_html"><span class="glyphicon glyphicon-font" rel="tooltip"
    title="View"></span></a>
    <a data-toggle="tab" href="#{$resourceKey}_info"><span class="glyphicon glyphicon-info-sign" rel="tooltip"
    title="Info"></span></a>
    <span class="edit">
        <a target="_blank" href="/dev/edit/index.php?file={$codeFile}"><span class="glyphicon glyphicon-edit"
        rel="tooltip" title="Code ({$codeFile})"></span></a>
        <a target="_blank" href="/dev/edit/index.php?file={$templateFile}"><span class="glyphicon glyphicon-file"
        rel="tooltip" title="Template ({$templateFile})"></span></a>
    </span>
</div>

<div class="tab-content frame">
    <div id="{$resourceKey}_body" class="tab-pane fade active in">
    <!-- resource_body_start -->
EOT;
        $result = $rmReturn($result);
        $result .= $body;
        $label = <<<EOT
<!-- resource_body_end -->
<!-- /resource:'{$resourceName}' -->
<!-- resource_tab_start -->
    </div>
    <div id="{$resourceKey}_var" class="tab-pane">
        <div class="tab-wrap">
            <span class="badge badge-info">Resource state</span><br>{$var}
        </div>
    </div>
    <div id="{$resourceKey}_html" class="tab-pane">
        <div class="tab-wrap">
            <span class="badge badge-info">Resource representation</span><br>{$resourceBodyHtml}
        </div>
    </div>
    <div id="{$resourceKey}_info" class="tab-pane">
        <div class="tab-wrap">{$info}</div>
    </div>
</div>
</div>
<!-- resource_tab_end -->
EOT;
        $result .= $rmReturn($label);

        return $result;
    }

    /**
     * Return var
     *
     * @param mixed $body
     *
     * @return bool|float|int|mixed|string
     * @return string
     */
    private function getVar($body)
    {
        if (is_scalar($body)) {
            return $body;
        }
        $isTraversable = (is_array($body) || $body instanceof Traversable);
        if (!$isTraversable) {
            return '-';
        }
        array_walk_recursive(
            $body,
            function (&$value) {
                if ($value instanceof Request) {
                    $value = '(Request)' . $value->toUri();
                }
                if ($value instanceof ResourceObject) {
                    $value = $value->body;
                }
                if (is_object($value)) {
                    /** @var $value object */
                    $value = '(object) ' . get_class($value);
                }
            }
        );

        return highlight_string(var_export($body, true), true);
    }

    /**
     * Return resource meta info
     *
     * @param ResourceObject $resourceObject
     *
     * @return string
     */
    private function getResourceInfo(ResourceObject $resourceObject)
    {
        $info = $this->getParamsInfo($resourceObject);
        $info .= $this->getInterceptorInfo($resourceObject);
        $info .= $this->getCacheInfo($resourceObject);
        $info .= $this->getProfileInfo($resourceObject);

        return $info;
    }

    /**
     * Return method invocation arguments info
     *
     * @param ResourceObject $resourceObject
     *
     * @return string
     * @return string
     */
    private function getParamsInfo(ResourceObject $resourceObject)
    {
        $result = self::BADGE_ARGS . self::DIV_WELL;
        if (isset($resourceObject->headers[DevInvoker::HEADER_PARAMS])) {
            $params = json_decode($resourceObject->headers[DevInvoker::HEADER_PARAMS], true);
        } else {
            $params = [];
        }
        foreach ($params as $param) {
            if (is_scalar($param)) {
                $type = gettype($param);
            } elseif (is_object($param)) {
                $type = get_class($param);
            } elseif (is_array($param)) {
                $type = 'array';
                $param = print_r($param, true);
            } elseif (is_null($param)) {
                $type = 'null';
            } else {
                $type = 'unknown';
            }
            $param = htmlspecialchars($param, ENT_QUOTES, "UTF-8");
            $paramInfo = "<li>($type) {$param}</li>";
        }
        if ($params === []) {
            $paramInfo = 'void';
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $result .= "<ul>{$paramInfo}</ul>";

        return $result . '</div>';
    }

    /**
     * Return resource meta info
     *
     * @param ResourceObject $resourceObject
     *
     * @return string
     */
    private function getInterceptorInfo(ResourceObject $resourceObject)
    {
        $result = self::BADGE_INTERCEPTORS . self::DIV_WELL;
        if (!isset($resourceObject->headers[DevInvoker::HEADER_INTERCEPTORS])) {
            return $result . 'n/a</div>';
        }
        $result .= '<ul class="unstyled">';
        $interceptors = json_decode($resourceObject->headers[DevInvoker::HEADER_INTERCEPTORS], true);
        $onGetInterceptors = isset($interceptors['onGet']) ? $interceptors['onGet'] : [];
        foreach ($onGetInterceptors as $interceptor) {
            $interceptorFile = (new ReflectionClass($interceptor))->getFileName();
            $interceptorFile = $this->makeRelativePath($interceptorFile);
            $result .= <<<EOT
<li style="height: 26px;"><a target="_blank" href="/dev/edit/index.php?file={$interceptorFile}"><span class="glyphicon-arrow-right"></span>{$interceptor}</a></li>
EOT;
        }
        $result .= '</ul></div>';

        return $result;
    }

    /**
     * Return cache info
     *
     * @param ResourceObject $resourceObject
     *
     * @return string
     * @return string
     */
    private function getCacheInfo(ResourceObject $resourceObject)
    {
        $cache = isset($resourceObject->headers[CacheLoader::HEADER_CACHE]) ? json_decode(
            $resourceObject->headers[CacheLoader::HEADER_CACHE],
            true
        ) : false;
        $result = self::BADGE_CACHE . self::DIV_WELL;
        if ($cache === false) {
            return $result . 'n/a</div>';
        }
        $iconLife = self::ICON_LIFE;
        $iconTime = self::ICON_TIME;

        $life = $cache['life'] ? "{$cache['life']} sec" : 'Unlimited';
        if (isset($cache['context']) && $cache['context'] === 'W') {
            $result .= "Write {$iconLife} {$life}";
        } else {
            if ($cache['life'] === false) {
                $time = $cache['date'];
            } else {
                $created = new DateTime($cache['date']);
                $interval = new DateInterval("PT{$cache['life']}S");
                $expire = $created->add($interval);
                $time = $expire->diff(new DateTime('now'))->format('%h hours %i min %s sec left');
            }
            $result .= "Read {$iconLife} {$life} {$iconTime} {$time}";
        }

        return $result . '</div>';
    }

    /**
     * Return resource meta info
     *
     * @param ResourceObject $resourceObject
     *
     * @return string
     */
    private function getProfileInfo(ResourceObject $resourceObject)
    {
        // memory, time
        $result = self::BADGE_PROFILE . self::DIV_WELL;
        if (isset($resourceObject->headers[DevInvoker::HEADER_EXECUTION_TIME])) {
            $time = number_format($resourceObject->headers[DevInvoker::HEADER_EXECUTION_TIME], 3);
        } else {
            $time = 0;
        }
        if (isset($resourceObject->headers[DevInvoker::HEADER_MEMORY_USAGE])) {
            $memory = number_format($resourceObject->headers[DevInvoker::HEADER_MEMORY_USAGE]);
        } else {
            $memory = 0;
        }
        $result .= <<<EOT
<span class="icon-time"></span> {$time} sec <span class="icon-signal"></span> {$memory} bytes
EOT;
        // profile id
        if (isset($resourceObject->headers[DevInvoker::HEADER_PROFILE_ID])) {
            $profileId = $resourceObject->headers[DevInvoker::HEADER_PROFILE_ID];
            $result .= <<<EOT
<span class="icon-random"></span><a href="/xhprof_html/index.php?run={$profileId}&source=resource"> {$profileId}</a>
EOT;
        }
        $result .= '</div>';

        return $result;
    }
}
