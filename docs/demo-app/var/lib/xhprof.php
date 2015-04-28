<?php
/**
 * Profiler
 *
 * print [profile] link at the bottom of page if xhprof installed.
 *
 * usage:
 *
 * // at bootstrap
 * include /path/to/profile.php
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
ini_set('xhprof.output_dir', '/tmp');
$enable = extension_loaded('xhprof');
if (! $enable) {
    return;
}

// start
xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

// stop
register_shutdown_function(
    function () {
        $xhprof = xhprof_disable();
        if (! $xhprof) {
            error_log('xhprof failed in ' . __FILE__);

            return;
        }
        $id = (new XHProfRuns_Default)->save_run($xhprof, '');
        error_log("xhprof:{$id}");
    }
);
