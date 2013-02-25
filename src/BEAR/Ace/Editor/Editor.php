<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Ace\Editor;

/**
 * Ace (Ajax.org Cloud9 Editor)
 *
 * @see http://ace.ajax.org/
 */
class Editor
{
    /**
     * @var string base path
     */
    protected $base;

    /**
     * @var string relative path
     */
    protected $path;

    /**
     * @var string full file path
     */
    protected $file;

    /**
     * @var int line number
     */
    protected $line;

    /**
     * @var string message
     */
    protected $message;

    /**
     * @var string save url
     */
    protected $saveUrl = 'save.php';

    /**
     * @param $base
     *
     * @return Editor
     */
    public function setBasePath($base)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * @param $path
     *
     * @return Editor
     * @throws \OutOfRangeException
     * @throws \InvalidArgumentException
     */
    public function setPath($path)
    {
        $this->path = $path;
        $fullPath = "{$this->base}/{$path}";

        // readable ?
        if (!is_readable($fullPath)) {
            throw new \InvalidArgumentException("Not found. {$path} : {$fullPath} is not readable.");
        }
        // allow only under project
        if (strpos($fullPath, $this->base) !== 0) {
            throw new \OutOfRangeException($fullPath);
        }

        $this->file = $fullPath;

        return $this;
    }

    /**
     * @param $line
     *
     * @return Editor
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @param $message
     *
     * @return Editor
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param $saveUrl
     *
     * @return Editor
     */
    public function setSaveUrl($saveUrl)
    {
        $this->saveUrl = $saveUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $fullPath = $this->file;
        $line = $this->line;
        $relativePath = $this->path;

        // set variable for view
        $view = [];
        $view['file'] = $fullPath;
        $view['file_path'] = $relativePath;
        $view['line'] = $line;
        $view['file_contents'] = htmlspecialchars(file_get_contents($fullPath));
        $id = md5($fullPath);
        $view['mod_date'] = date(DATE_RFC822, filemtime($fullPath));
        $view['is_writable'] = is_writable($fullPath);
        $view['is_writable_label'] = $view['is_writable'] ? "" : " Read Only";
        $view['auth'] = md5(session_id() . $id);
        $view['error'] = (isset($_GET['error'])) ? ($_GET['error']) : '';

        // get html view
        $view = $this->getView($view);

        return $view;
    }

    /**
     * Save contents
     *
     * @param $contents
     *
     * @return string
     */
    public function save($contents)
    {
        $result = (string)file_put_contents($this->file, $contents, LOCK_EX | FILE_TEXT);
        $log = "codeEdit saved:{$this->path} addr:{$_SERVER["REMOTE_ADDR"]} result:{$result}";

        return $log;
    }

    /**
     * @param array $view
     *
     * @return string
     */
    private function getView(array $view)
    {
        $view['is_read_only'] = $view['is_writable'] ? 0 : 1;
        $view['is_writable_label'] = $view['is_writable'] ? 'reset' : 'read only';
        $view['line'] = $view['line'] ? "({$view['line']})" : 0;
        $view['error'] = $view['error'] ? '' : '';
        $view['save_url'] = $this->saveUrl;
        $view['message'] = $this->message ? "<span class=\"error\">{$this->message}</span>" : '';

        return require __DIR__ . '/view.php';
    }
}
