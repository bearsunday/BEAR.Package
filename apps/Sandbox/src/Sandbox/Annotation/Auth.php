<?php

namespace Sandbox\Annotation;

use BEAR\Sunday\Annotation\AnnotationInterface;

/**
 * Auth
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Auth implements AnnotationInterface
{
    /**
     * Realm for authentication
     *
     * @var string
     */
    public $realm;
}
