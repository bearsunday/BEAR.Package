<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\DevWeb\Editor;

/**
 * File tree
 *
 * @package    BEAR.Package
 * @subpackage Editor
 */
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
    public $cmd = '';

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
            $path = $this->root;
        }
        $this->cmd .= "addTree('{$placeholder}', '{$path}/', {$isDir}, '{$label}');";
        return $this;
    }

    /**
     * @param $initialOpeningFile
     *
     * @return string
     */
    public function getJsCode($initialOpeningFile)
    {
        return "$(document).ready( function() {{$this->cmd}load(\"$initialOpeningFile\")});";
    }
}
