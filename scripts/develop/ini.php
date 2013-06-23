<?php

error_reporting(E_ALL);

ini_set('display_errors', 1);
ini_set('xhprof.output_dir', sys_get_temp_dir());
ini_set('xdebug.collect_params', 0);
ini_set('xdebug.max_nesting_level', 500);
ini_set('xdebug.var_display_max_depth', 1);
ini_set('xdebug.file_link_format', '/dev/edit/?file=%f&line=$l');
