<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\LogWriterInterface;
use BEAR\Resource\RequestInterface;
use FirePHP;
use Traversable;

/**
 * Fire logger
 *
 * @codeCoverageIgnore
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
        $this->fire = $fire ? : FirePHP::getInstance(true);
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
     *
     * @return void
     */
    private function fireResourceObjectLog(ResourceObject $result)
    {
        /** @noinspection PhpParamsInspection */
        $this->fire->log($result->code, 'code');
        $this->fireHeader($result);
        $this->fireBody($result);
        $this->fireLink($result);
        $this->fireView($result);
    }

    /**
     * @param ResourceObject $result
     *
     * @return void
     */
    private function fireHeader(ResourceObject $result)
    {
        $headers = [];
        $headers[] = ['name', 'value'];
        foreach ($result->headers as $name => $value) {
            $headers[] = [$name, $value];
        }
        $this->fire->table('headers', $headers);
    }

    /**
     * @param ResourceObject $result
     *
     * @return void
     */
    private function fireBody(ResourceObject $result)
    {
        $body = $this->normalize($result->body);
        $isTable = is_array($body) && isset($body[0]) && isset($body[1]) && (array_keys($body[0]) === array_keys($body[1]));
        if (! $isTable) {
            $this->fire->log($body, 'body');
            return;
        }
        $table = [];
        $table[] = (array_values(array_keys($body[0])));
        foreach ((array) $body as $val) {
            $table[] = array_values((array) $val);
        }
        $this->fire->table('body', $table);
    }

    /**
     * Format log data
     *
     * @param mixed $body
     *
     * @return mixed
     * @todo scan all prop like print_o, then eliminate all resource/PDO/etc.. unrealisable objects...not like this.
     */
    public function normalize(&$body)
    {
        if (!(is_array($body) || $body instanceof Traversable)) {
            return $body;
        }
        array_walk_recursive(
            $body,
            function (&$value) {
                if ($value instanceof RequestInterface) {
                    $value = '(Request) ' . $value->toUri();
                }
                if ($value instanceof ResourceObject) {
                    /** @var $value ResourceObject */
                    $value = '(ResourceObject) ' . get_class($value) . json_encode($value->body);
                }
                if (is_object($value)) {
                    $value = '(object) ' . get_class($value);
                }
            }
        );

        return $body;
    }

    /**
     * @param ResourceObject $result
     *
     * @return void
     */
    private function fireLink(ResourceObject $result)
    {
        $links = [['rel', 'uri']];
        foreach ($result->links as $rel => $uri) {
            $links[] = [$rel, $uri];
        }
        if (count($links) > 1) {
            $this->fire->table('links', $links);
        }
    }

    /**
     * @param ResourceObject $result
     *
     * @return void
     */
    private function fireView(ResourceObject $result)
    {
        $this->fire->group('view', ['Collapsed' => true]);
        /** @noinspection PhpParamsInspection */
        $this->fire->log($result->view);
        $this->fire->groupEnd();
    }
}
