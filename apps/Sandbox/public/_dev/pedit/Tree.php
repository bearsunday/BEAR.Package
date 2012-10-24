<?php
require_once 'ini.php';

class BEAR_Tree
{

    /**
     * Cmd
     *
     * @var string
     */
    var $_cmd = '';

    /**
     * Add tree
     *
     * @param unknown_type $placeholder
     * @param unknown_type $path
     * @param unknown_type $label
     *
     * @return void
     */
    public function tree($placeholder, $path, $label)
    {
        if (is_array($path)) {
            $isDir = 'false';
            foreach ($path as &$file) {
                if (strpos($file, _BEAR_EDIT_ROOT_PATH, 0) === 0) {
                    $file = substr($file, strlen(_BEAR_EDIT_ROOT_PATH));
                }
            }
            $path = serialize($path);
        } else {
            $isDir = 'true';
            if (strpos($path, _BEAR_EDIT_ROOT_PATH, 0) === 0) {
                $path = substr($path, strlen(_BEAR_EDIT_ROOT_PATH));
            }
        }
        $this->_cmd .= "addTree('{$placeholder}', '{$path}/', {$isDir}, '{$label}');";
    }

    /**
     *
     * @return void
     */
    public function exec($initialOpeningFile)
    {
        echo "$(document).ready( function() {{$this->_cmd}load(\"$initialOpeningFile\")});";
    }
}
