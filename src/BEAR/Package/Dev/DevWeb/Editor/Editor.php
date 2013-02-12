<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\DevWeb\Editor;

/**
 * Online editor
 */
class Editor
{
    /**
     * @return array
     */
    public function getView()
    {
        list($fullPath, $line, $relativePath) = $this->getInput();
        echo '<i></i>'; // ???
        // set variable for view
        $view = [];
        $view['file'] = $fullPath;
        $view['file_path'] = $relativePath;
        $view['line'] = $line;
        $view['file_contents'] = htmlspecialchars(file_get_contents($fullPath));
        $id = md5($fullPath);
        $view['mod_date'] = date(DATE_RFC822, filemtime($fullPath));
        $view['owner_info'] = function_exists('posix_getpwuid') ? posix_getpwuid(
            fileowner($fullPath)
        ) : array('name' => 'n/a');
        $fileperms = substr(decoct(fileperms($fullPath)), 2);
        ;
        $view['is_writable'] = is_writable($fullPath);
        $view['is_writable_label'] = $view['is_writable'] ? "" : " Read Only";
        $view['auth'] = md5(session_id() . $id);
        $view['error'] = (isset($_GET['error'])) ? ($_GET['error']) : '';

        return $view;
    }

    /**
     * @return string
     */
    public function save()
    {
        list($fullPath, $line, $relativePath) = $this->getInput();
        $contents = $_POST['contents'];
        $result = (string)file_put_contents($fullPath, $contents, LOCK_EX | FILE_TEXT);
        $log = "codeEdit saved:$fullPath addr:{$_SERVER["REMOTE_ADDR"]} result:{$result}";

        return $log;
    }

    /**
     * Return validated path info
     *
     * @return array [$fullPath, $line, $relativePath]
     */
    private function getInput()
    {
        // input
        $line = isset($_GET['line']) ? $_GET['line'] : 0;
        $path = isset($_REQUEST['file']) ? $_REQUEST['file'] : false;
        $rootDir = isset($_ENV['SUNDAY_ROOT']) ? $_ENV['SUNDAY_ROOT'] : dirname(
            dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))
        );

        if (!isset($_ENV['SUNDAY_DISABLE_FULL_PATH_FILE_EDIT']) && is_readable($path)) {
            return [$path, $line, $path];
        }
        // disallow full path
        $fullPath = $rootDir . $path;
        $relativePath = $path;

        // readable ?
        if (!is_readable($fullPath)) {
            throw new \InvalidArgumentException("Not found. {$path} : {$fullPath} is not readable.");
        }
        // allow only under project
        if (strpos($fullPath, $rootDir) !== 0) {
            throw new \OutOfRangeException($fullPath);
        }

        return [$fullPath, $line, $relativePath];
    }
}
