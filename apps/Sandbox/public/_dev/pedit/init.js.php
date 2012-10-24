<?php

namespace pedit;

$root = require __DIR__ . '/ini.php';

class FileTree
{
    private $root;

    /**
     * @param $root
     */
    public function __construct($root)
    {
        $this->root = $root;
    }

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
                if (strpos($file, $this->root, 0) === 0) {
                    $file = substr($file, strlen($this->root));
                }
            }
            $path = serialize($path);
        } else {
            $isDir = 'true';
            if (strpos($path, $this->root, 0) === 0) {
                $path = substr($path, strlen($this->root));
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

$projectDir = '';

// Treeを描画
$tree = new FileTree($root);
//$tree->tree('#container_id1', $files['page'], '<span class=\"tree_label\">Page</span>');
//$tree->tree('#container_id2', $files['ro'], '<span class=\"tree_label\">Resource</span>');
//$tree->tree('#container_id3', $files['view'], '<span class=\"tree_label\">View template</span>');
$tree->tree('#container_id', $projectDir , '<span class=\"tree_label\">Project</span>');
$initialOpeningFile ='src.php';
$tree->exec($initialOpeningFile);