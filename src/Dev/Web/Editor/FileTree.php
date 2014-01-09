<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Web\Editor;

class FileTree
{
    /**
     * Cmd
     *
     * @var string
     */
    public $cmd = '';

    /**
     * @var string
     */
    private $root;

    /**
     * @param string $root
     */
    public function __construct($root)
    {
        $this->root = $root;
    }

    /**
     * Add tree
     *
     * @param string $placeholder
     * @param string $path
     * @param string $label
     *
     * @return FileTree
     */
    public function tree($placeholder, $path, $label)
    {
        list($isDir, $path) = $this->getPath($path);
        $this->cmd .= "addTree('{$placeholder}', '{$path}/', {$isDir}, '{$label}');";

        return $this;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    private function getPath($path)
    {
        if (! is_array($path)) {
            $isDir = 'true';
            $path = $this->root;

            return [$isDir, $path];
        }
        $isDir = 'false';
        foreach ($path as &$file) {
            if (strpos($file, $this->root, 0) === 0) {
                $file = substr($file, strlen($this->root));
            }
        }
        $path = serialize($path);

        return [$isDir, $path];
    }

    /**
     * @param string $initialOpeningFile
     *
     * @return string
     */
    public function getJsCode($initialOpeningFile)
    {
        return "$(document).ready( function () {{$this->cmd}load(\"$initialOpeningFile\")});";
    }
}
