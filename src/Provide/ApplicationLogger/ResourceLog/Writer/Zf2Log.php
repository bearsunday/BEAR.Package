<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\LogWriterInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\ProviderInterface;

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
        $logger = $this->provider->get();
        $this->pageId = rtrim(base64_encode(pack('H*', md5($_SERVER['REQUEST_TIME_FLOAT']))), '=');
        $id = "{$this->pageId}";
        /** @var $logger \Zend\Log\LoggerInterface */
        $msg = "id:{$id}\treq:" . $request->toUriWithMethod();
        $msg .= "\tcode:" . $result->code;
        $msg .= "\tbody:" . json_encode($result->body);
        $msg .= "\theader:" . json_encode($result->headers);
        $path = $this->getPath(isset($_SERVER['PATH_INFO']));
        $msg .= "\tpath:$path";
        try {
            $logger->info($msg, ['page' => $this->pageId]);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $hasServerInfo
     *
     * @return string
     */
    private function getPath($hasServerInfo)
    {
        if (!$hasServerInfo) {
            return '/';
        }
        $path = $_SERVER['PATH_INFO'];
        $path .= $_GET ? '?' : '';
        $path .= http_build_query($_GET);

        return $path;
    }
}
