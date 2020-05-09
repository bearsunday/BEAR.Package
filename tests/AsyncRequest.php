<?php

declare(strict_types=1);

namespace BEAR\Package;

final class AsyncRequest
{
    public function __invoke(string $cmd) : void
    {
        $procs = [];
        try {
            for ($i = 0; $i < 16; ++$i) {
                $pipes = [];
                $procs[] = proc_open(
                    $cmd,
                    [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
                    $pipes
                );
            }
            for (; ;) {
                foreach ($procs as $p) {
                    assert(is_resource($p));
                    if (proc_get_status($p)['running']) {
                        sleep(1);

                        continue 2;
                    }
                }

                break;
            }
        } finally {
            foreach ($procs as $p) {
                assert(is_resource($p));
                proc_close($p);
            }
        }
    }
}
