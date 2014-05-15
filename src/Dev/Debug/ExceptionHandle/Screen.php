<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Debug\ExceptionHandle;

/**
 * Interface for exception handler
 */
class Screen
{
    /**
     * @var array
     */
    private $propTables = [];

    /**
     * Return trace array as string
     *
     * @param array $trace
     *
     * @return string
     */
    public function getTraceAsJsString(array $trace)
    {
        $stack = $this->getStack($trace);
        $cnt = 0;
        $html = '';
        foreach ($stack as $row) {
            $cnt++;
            foreach ($row as &$value) {
                if (is_object($value)) {
                    $value = get_class($value);
                }
            }
            if (isset($row['file']) && is_file($row['file'])) {
                $html .= "<li>";
                $html .= "<a href=\"#\" class=\"\" data-toggle=\"collapse\" data-target=\"#source{$cnt}\">";
                $html .= "<code>{$row['statement']}</code>";
                $html .= "</a>";
                $html .= "<a href=\"#\" class=\"\" data-toggle=\"collapse\" data-target=\"#args{$cnt}\">";
                $html .= "<span class=\"params\"><span class=\"glyphicon glyphicon-search\"></span></span>";
                $html .= "</a>";
                $html .= "{$row['file']} : {$row['line']}  ";
                $html .= "<a target=\"code_edit\" href=\"/dev/edit/index.php?file={$row['file']}&line={$row['line']}\"><span class=\"glyphicon glyphicon-edit\"></span></a>";
                $args = isset($row['args']) ? $this->getArgsAsString($row['args']) : '';
                $html .= "</li>";
                $html .= "<div id=\"source{$cnt}\" class=\"collapse out\">{$row['source']}</div>";
                $html .= "<div id=\"args{$cnt}\" class=\"collapse out\">{$args}</div>";
            }
        }

        return $html;
    }

    /**
     * Return trace
     *
     * @param $trace
     *
     * @return array
     */
    private function getStack($trace)
    {
        $stack = [];
        foreach ($trace as $row) {
            if (isset($row['class'])) {
                $row['type'] = isset($row['type']) && $row['type'] === 'dynamic' ? '->' : '::';
            }
            if (isset($row['params'])) {
                $row['args'] = $row['params'];
            }
            $this->rowStatement($row);
            $row['source'] = isset($row['file']) ? $this->getFiles($row['file'], $row['line']) : 'n/a';
            $stack[] = $row;
        }

        return $stack;
    }

    /**
     * Add statement
     *
     * @param $row
     */
    private function rowStatement(&$row)
    {
        if (isset($row['class'])) {
            $row['statement'] = "{$row['class']}{$row['type']}{$row['function']}()";
            return;
        } elseif (isset($row['function'])) {
            $row['statement'] = "{$row['function']}()";
            return;
        } elseif (isset($row['include_filename'])) {
            $row['statement'] = "include_filename {$row['include_filename']}";
            return;
        }
        $row['statement'] = "...";
    }

    /**
     * Return files
     *
     * @param     $file
     * @param     $line
     * @param int $num
     *
     * @return string
     */
    private function getFiles($file, $line, $num = 6)
    {

        if (!file_exists($file) || $line === 0) {
            return '<pre>n/a</pre>';
        }
        $result = '<div class="file-summary">';
        $files = file($file);
        $fileArray = array_map('htmlspecialchars', $files);
        $fileArray[$line - 1] = "<span class=\"hit-line\">{$fileArray[$line - 1]}</span>";
        $shortListArray = array_slice($fileArray, $line - $num, $num * 2);
        $shortListArray[$num - 1] = '<strong>' . $fileArray[$line - 1] . '</strong>';
        $shortList = implode('', $shortListArray);
        $shortList = '<pre class="short-list" style="background-color: #F0F0F9;">' . $shortList . '</pre>';
        $result .= $shortList . '</div>';

        return $result;
    }

    /**
     * Return arguments as formatted string
     *
     * @param array $args
     *
     * @return string
     */
    private function getArgsAsString(array &$args)
    {
        if (!$args) {
            return '<i>(void)</i>';
        }
        $html = '<table class="table table-condensed table-bordered params-table">';
        $divObject = '';
        foreach ($args as $index => $arg) {
            $divObject = '';
            $type = gettype($arg);
            if (is_object($arg)) {
                $divObject = $this->divObject($arg);
                $objHash = spl_object_hash($arg);
                $link = "<a href=\"#\" class=\"\" data-toggle=\"collapse\" data-target=\"#obj{$objHash}\">";
                $arg = $link . '(object) ' . $this->getObjectName($arg) . '</a>';
            }
            if (is_array($arg)) {
                $this->makeArgsElementsScalar($arg);
            }
            $html .= "<tr><td>{$index}</td><td>{$type}</td></td><td>{$arg}</td></tr>";
        }
        $html .= '</table>';
        return $html . $divObject;
    }

    /**
     * Return object as string
     *
     * @param $obj
     *
     * @return string
     */
    private function divObject($obj)
    {
        $props = (new \ReflectionObject($obj))->getProperties();
        $index = spl_object_hash($obj);
        $propTables = "<div id=\"obj{$index}\" class=\"collapse out\">";
        $propTables .= $this->getObjectName($obj);
        $propTables .= '<table class="table table-condensed table-bordered params-table">';
        foreach ($props as $prop) {
            $modifier = \Reflection::getModifierNames($prop->getModifiers())[0];
            $name = $prop->getName();
            $prop->setAccessible(true);
            $value = $prop->getValue($obj);
            if (is_object($value) || 1 && !isset($this->propTables[$index])) {
                $this->propTables[$index] = '';
                //$this->divObject($value);
                $value = $this->getObjectName($obj);
            }
            if (is_array($value)) {
                $this->makeArgsElementsScalar($value);
                $value = var_export($value, true);
            }
            $propTables .= "<tr><td>{$modifier}</td><td>\${$name}</td><td>{$value}</td></tr>";
        }
        $propTables .= "</table>";
        $propTables .= "</div>";
        $this->propTables[$index] = $propTables;
        return $propTables;
    }

    /**
     * Return object name
     *
     * @param $obj
     *
     * @return string
     */
    private function getObjectName($obj)
    {
        return '<strong>' . get_class($obj) . '</strong><span class="weak">#' . spl_object_hash($obj) . '</span>';
    }

    /**
     * @param array $args
     */
    private function makeArgsElementsScalar(array &$args)
    {
        foreach ($args as &$arg) {
            if (is_object($arg)) {
                $arg = $this->getObjectName($arg);
            }
            if (is_array($arg)) {
                $arg = sprintf('*array(%d)', count($arg));
            }
        }
        $args = print_r($args, true);
    }

    /**
     * Return headers
     *
     * @param \Exception $e
     * @param string     $type
     *
     * @return string
     */
    public function getHeader(\Exception $e, $type = "danger")
    {
        $title = get_class($e);
        $subTitle = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        return <<<EOT
      <div class="alert alert-block alert-{$type} fade in">
        <a class="close" data-dismiss="alert" href="#">&times;</a>
        <h2 class="alert-heading">{$title}</h2>
        <h3>{$subTitle}</h3>
        <div>in {$file} on line {$line}</div>
      </div>

EOT;
    }

    /**
     * Return editor link
     *
     * @param      $file
     * @param      $line
     * @param null $systemRoot
     *
     * @return string
     */
    public function getEditorLink($file, $line, $systemRoot = null)
    {
        $href = '/dev/edit/index.php?file=';
        $href .= $systemRoot ? str_replace($systemRoot, '', $file) : $file;
        $href .= "&line={$line}";
        $link = "<a target=\"code_edit\" href=\"{$href}\" >{$file} : {$line}</a>";
        return $link;
    }
}
