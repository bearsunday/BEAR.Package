<?php

namespace pedit;

require __DIR__ . '/ini.php';
$projectDir = '/Users/akihito/git/BEAR.Resource';

class FileTree
{

    /**
     * Cmd
     *
     * @var string
     */
    var $_cmd = '';

    /**
     * @param $placeholder
     * @param $path
     * @param $label
     *
     * @return FileTree
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

        return $this;
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

// Treeを描画
$tree = new FileTree;
//$tree->tree('#container_id1', $files['page'], '<span class=\"tree_label\">Page</span>');
//$tree->tree('#container_id2', $files['ro'], '<span class=\"tree_label\">Resource</span>');
//$tree->tree('#container_id3', $files['view'], '<span class=\"tree_label\">View template</span>');
$tree->tree('#container_id', $projectDir , '<span class=\"tree_label\">Project</span>');
$initialOpeningFile ='src.php';
$tree->exec($initialOpeningFile);