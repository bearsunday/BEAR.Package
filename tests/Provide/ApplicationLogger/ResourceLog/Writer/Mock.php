<?php
namespace BEAR\Package\tests\Provide\ApplicationLogger\ResourceLog\Writer;

class Mock extends \BEAR\Resource\AbstractObject
{
    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];

        return $this;
    }
}
