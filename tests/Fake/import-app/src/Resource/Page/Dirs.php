<?php

declare(strict_types=1);

namespace Import\HelloWorld\Resource\Page;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Resource\ResourceObject;

/** Answers where its own container says this application writes. */
class Dirs extends ResourceObject
{
    /** @var array{tmpDir: string, logDir: string} */
    public $body;

    public function __construct(private AbstractAppMeta $appMeta)
    {
    }

    /** @return static */
    public function onGet(): ResourceObject
    {
        $this->body = [
            'tmpDir' => $this->appMeta->tmpDir,
            'logDir' => $this->appMeta->logDir,
        ];

        return $this;
    }
}
