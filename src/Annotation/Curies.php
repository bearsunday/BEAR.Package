<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Annotation;

use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("CLASS")
 * @Qualifier
 */
final class Curies
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $href;

    /**
     * @var bool
     */
    public $template;
}
