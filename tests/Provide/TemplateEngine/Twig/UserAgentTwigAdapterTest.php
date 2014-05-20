<?php
/**
 * This file is part of the {package} package
 *
 * @package {package}
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Provide\TemplateEngine\Twig;


use Aura\Web\WebFactory;
use Aura\Web\Request\Client;
use Twig_Environment;
use Twig_Loader_Filesystem;

class UserAgentTwigAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserAgentTwigAdapter
     */
    private $adapter;

    /**
     * @var template name without extention
     */
    private $tpl;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * mobile UA data
     * @see https://github.com/auraphp/Aura.Web/blob/efb816478df7dda89f64980e0f2330d4071850be/tests/src/Request/ClientTest.php
     *
     * @return array
     */
    public function provider()
    {
        return [
            array('Android', 'Mozilla/5.0 (Linux; U; Android 2.1; en-us; Nexus One Build/ERD62) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17 –Nexus'),
            array('BlackBerry', 'BlackBerry8330/4.3.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/105'),
            array('iPhone', 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16'),
            array('iPad', 'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; es-es) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405'),
            array('Blazer', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows 98; PalmSource/Palm-D062; Blazer/4.5) 16;320x320'),
            array('Brew', 'Mozilla/5.0 (compatible; Teleca Q7; Brew 3.1.5; U; en) 240X400 LGE VX9700'),
            array('IEMobile', 'LG-CT810/V10x IEMobile/7.11 Profile/MIDP-2.0 Configuration/CLDC-1.1 Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 7.11)'),
            array('iPod', 'Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A100a Safari/419.3 '),
            array('KDDI', 'KDDI-KC31 UP.Browser/6.2.0.5 (GUI) MMP/2.0'),
            array('Kindle', 'Mozilla/4.0 (compatible; Linux 2.6.22) NetFront/3.4 Kindle/2.0 (screen 600x800)'),
            array('Maemo', 'Mozilla/4.0 (compatible; MSIE 6.0; ; Linux armv5tejl; U) Opera 8.02 [en_US] Maemo browser 0.4.31 N770/SU-18'),
            array('MOT-' ,'MOT-L6/0A.52.45R MIB/2.2.1 Profile/MIDP-2.0 Configuration/CLDC-1.1'),
            array('Nokia', 'Mozilla/4.0 (compatible; MSIE 5.0; Series80/2.0 Nokia9300/05.22 Profile/MIDP-2.0 Configuration/CLDC-1.1)'),
            array('SymbianOS', 'Mozilla/5.0 (SymbianOS/9.1; U; en-us) AppleWebKit/413 (KHTML, like Gecko) Safari/413 es61i'),
            array('UP.Browser', 'OPWV-SDK UP.Browser/7.0.2.3.119 (GUI) MMP/2.0 Push/PO'),
            array('UP.Link', 'HTC-ST7377/1.59.502.3 (67150) Opera/9.50 (Windows NT 5.1; U; en) UP.Link/6.3.1.17.0'),
            array('Opera Mobi', 'Opera/9.80 (S60; SymbOS; Opera Mobi/499; U; en-GB) Presto/2.4.18 Version/10.00'),
            array('Opera Mini', 'Opera/9.60 (J2ME/MIDP; Opera Mini/4.2.13918/488; U; en) Presto/2.2.0'),
            array('webOS', 'Mozilla/5.0 (webOS/1.0; U; en-US) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pre/1.0'),
            array('Playstation', 'Mozilla/5.0 (PLAYSTATION 3; 1.00)'),
            array('Windows CE', 'Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; Sprint:PPC-6700; PPC; 240x320)'),
            array('Polaris', 'LG-LX600 Polaris/6.0 MMP/2.0 Profile/MIDP-2.1 Configuration/CLDC-1.1'),
            array('SEMC', 'SonyEricssonK608i/R2L/SN356841000828910 Browser/SEMC-Browser/4.2 Profile/MIDP-2.0 Configuration/CLDC-1.1'),
            array('NetFront', 'Mozilla/4.0 (compatible;MSIE 6.0;Windows95;PalmSource) Netfront/3.0;8;320x320'),
            array('Fennec', 'Mozilla/5.0 (X11; U; Linux armv61; en-US; rv:1.9.1b2pre) Gecko/20081015 Fennec/1.0a1'),
        ];
    }
    protected function setUp()
    {
        $loader = new Twig_Loader_Filesystem(['/', __DIR__]);
        $this->twig = new Twig_Environment($loader);
        $client = new Client([], [], [], []);
        $this->adapter = new UserAgentTwigAdapter($this->twig, $client);
        $this->tpl = __DIR__ . '/test.';

    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\TemplateEngine\Twig\UserAgentTwigAdapter', $this->adapter);
    }

    public function testAssign()
    {
        $this->adapter->assign('greeting', 'adios');
        $result = $this->adapter->fetch($this->tpl);
        $this->assertSame('greeting is adios', $result);
    }

    /**
     * @dataProvider provider
     */
    public function testMobileAssign($ua)
    {
        $server['HTTP_USER_AGENT'] = $ua;
        $client = new Client($server, [], [], []);
        $adapter = new UserAgentTwigAdapter($this->twig, $client);
        $adapter->assign('greeting', 'adios');
        $result = $adapter->fetch($this->tpl);
        $this->assertSame('mobile greeting is adios', $result);
    }

}