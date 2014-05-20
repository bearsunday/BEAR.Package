<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\UserAgent;

use Aura\Web\Request\Client;
use Ray\Di\ProviderInterface;

class ClientProvider implements ProviderInterface
{
    /**
     * @var array
     */
    private $mobileAgents = [];

    /**
     * @param $mobileAgents
     *
     * @Inject(optional=true)
     * @Named("mobile_agents")
     */
    public function __construct($mobileAgents)
    {
        $this->mobileAgents = $mobileAgents;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return new Client($_SERVER, $this->mobileAgents);
    }
}
