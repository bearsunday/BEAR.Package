<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Web;

use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Exception\MethodNotAllowed;
use BEAR\Sunday\Web\GlobalsInterface;

/**
 * Globals
 *
 * Emulates web $GLOBALS in CLI
 *
 * @package    BEAR.Sunday
 * @subpackage Framework
 */
final class Globals implements GlobalsInterface
{
    /**
     * Constructor
     *
     * @param array $argv
     *
     * @throws BadRequest
     * @throws MethodNotAllowed
     */
    public function get(array $argv)
    {
        if (count($argv) < 3) {
            throw new BadRequest('Usage: [get|post|put|delete] [uri]');
        }
        $isMethodAllowed = in_array($argv[1], ['get', 'post', 'put', 'delete', 'options']);
        if (!$isMethodAllowed) {
            throw new MethodNotAllowed($argv[1]);
        }
        $globals['_SERVER']['REQUEST_METHOD'] = $argv[1];
        $globals['_SERVER']['REQUEST_URI'] = parse_url($argv[2], PHP_URL_PATH);
        parse_str(parse_url($argv[2], PHP_URL_QUERY), $get);
        $globals['_GET'] = $get;

        return $globals;
    }
}
