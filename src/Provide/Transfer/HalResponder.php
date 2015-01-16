<?php
/**
 * This file is part of the *** package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Transfer;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Transfer\TransferInterface;

class HalResponder implements TransferInterface
{
    public function __invoke(ResourceObject $resourceObject, array $server)
    {
        // code
        http_response_code($resourceObject->code);

        // header
        foreach ($resourceObject->headers as $label => $value) {
            header("{$label}: {$value}", false);
        }

        // application/hal+json header
        //
        // @see https://github.com/mikekelly/hal_specification/blob/master/hal_specification.md
        // @see http://tools.ietf.org/html/draft-kelly-json-hal-06
        header('Content-type: application/hal+json');

        // body
        echo (string) $resourceObject;
    }
}
