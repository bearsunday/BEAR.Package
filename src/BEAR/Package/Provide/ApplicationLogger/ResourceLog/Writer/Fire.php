<?php
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
        $headers = [];
        $headers[] = ['name', 'value'];
        foreach ($result->headers as $name => $value) {
            $headers[] = [$name, $value];
        }
        $this->fire->table('headers', $headers);

        // body
        $body = $this->normalize($result->body);
        $isTable = is_array($body)
            && isset($body[0])
            && isset($body[1])
            && (array_keys($body[0]) === array_keys($body[1]));
        if ($isTable) {
            $table = [];
            $table[] = (array_values(array_keys($body[0])));
            foreach ((array)$body as $val) {
                $table[] = array_values((array)$val);
            }
            $this->fire->table('body', $table);
        } else {
            $this->fire->log($body, 'body');
        }

        // links
        $links = [['rel', 'uri']];
        foreach ($result->links as $rel => $uri) {
            $links[] = [$rel, $uri];
        }
        if (count($links) > 1) {
            $this->fire->table('links', $links);
        }
        $this->fire->group('view', ['Collapsed' => true]);
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
                if ($value instanceof \PDO || $value instanceof \PDOStatement) {
                    $value = '(PDO) ' . get_class($value);
                }
                if ($value instanceof \Doctrine\DBAL\Connection) {
                    $value = '(\Doctrine\DBAL\Connection) ' . get_class($value);
                }
                if (is_resource($value)) {
                    $value = '(resource) ' . gettype($value);
                }
                if (is_object($value)) {
                    $value = '(object) ' . get_class($value);
                }
            }
        );

        return $body;
    }
}
