<?php

declare(strict_types=1);

namespace BEAR\Package;

final class AsyncRun
{
    /**
     * @param array<string> $cmds
     */
    public function __invoke(array $cmds, string $errorLog) : int
    {
        $times = count($cmds);
        $procs = [];
        try {
            foreach ($cmds as $cmd) {
                $pipes = [];
                $proc = proc_open(
                    $cmd,
                    [STDIN, STDOUT, ['file', $errorLog, 'a']],
                    $pipes
                );
                if (! is_resource($proc)) {
                    throw new \RuntimeException;
                }
                $procs[] = $proc;
            }
            $exitCode = $completed = 0;
            do {
                foreach ($procs as $p) {
                    $status = proc_get_status($p);
                    if ($status['exitcode'] == -1) {
                        // over
                        continue;
                    }
                    if (! $status['running']) {
                        // just completed
                        $completed++;
                        $exitCode |= $status['exitcode'];
                    }
                    usleep(100 * 1000);
                }
                $isNotComletedAll = $completed !== $times;
            } while ($isNotComletedAll);
        } finally {
            foreach ($procs as $p) {
                proc_close($p);
            }
        }

        return $exitCode;
    }
}
