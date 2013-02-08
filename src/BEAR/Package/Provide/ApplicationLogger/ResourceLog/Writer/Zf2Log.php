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
use Ray\Di\Di\Inject;

final class Zf2Log implements LogWriterInterface
{
    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @param ProviderInterface $log
     *
     * @Inject
     */
    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function write(RequestInterface $request, ResourceObject $result)
    {
        $logger = $this->provider->get();
        /** @var $logger \Zend\Log\LoggerInterface */
        $msg = 'req:' . $request->toUriWithMethod() . "{\t}code:" . $result->code;
        $msg .= '{\t}body:' . json_encode($result->body);
        $logger->info($msg);
    }
}