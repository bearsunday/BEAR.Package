<?php
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
        $msg .= "\tcode:" . $result->code;
        $msg .= "\tbody:" . json_encode($result->body);
        $msg .= "\theader:" . json_encode($result->headers);
        if (isset($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
            $path .= $_GET ? '?' : '';
            $path .= http_build_query($_GET);
        } else {
            $path = '/';
        }
        $msg .= "\tpath:$path";
        $this->logger->info($msg, ['page' => $this->pageId]);
    }
}
