<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Twig;

use Aura\Web\Request\Client;
use Aura\Web\WebFactory;
use BEAR\Package\Provide\TemplateEngine\AdapterTrait;
use BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface;
use Twig_Environment;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\PostConstruct;

class UserAgentTwigAdapter implements TemplateEngineAdapterInterface
{
    use AdapterTrait;

    /**
     * File extension
     *
     * @var string
     */
    const EXT = 'twig';

    /**
     * Twig
     *
     * @var @return BEAR
     */
    private $twig;

    /**
     * @var array
     */
    private $values = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Twig_Environment $twig
     *
     * @Inject
     */
    public function __construct(Twig_Environment $twig, Client $client)
    {
        $this->twig = $twig;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($tplVar, $value)
    {
        $this->values[$tplVar] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function assignAll(array $values)
    {
        $this->values = $values + $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($tplWithoutExtension)
    {
        $this->template = $this->client->isMobile()? $this->getMobileTemplate($tplWithoutExtension) : $tplWithoutExtension . self::EXT;
        $this->fileExists($this->template);
        $template = $this->twig->loadTemplate($this->template);
        $rendered = $template->render($this->values);

        return $rendered;
    }

    /**
     * @param $tplWithoutExtension
     *
     * @return string
     */
    private function getMobileTemplate($tplWithoutExtension)
    {
        $template = $tplWithoutExtension . 'mobile.' . self::EXT;
        $this->fileExists($template);

        return $template;
    }
}
