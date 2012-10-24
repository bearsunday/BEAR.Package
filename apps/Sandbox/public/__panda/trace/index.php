<?php
require_once 'Panda/vendors/debuglib.php';
require_once 'Panda.php';
/**
 * Trace Page Class
 *
 */
class Panda_Trace_Page
{

    /**
     * Editor - None
     */
    const EDITOR_NONE = 0;

    /**
     * Editor - TextMate
     */
    const EDITOR_TEXTMATE = 1;

    /**
     * trace depth
     *
     * @var integer
     */
    private $_levelNum;

    /**
     * trace levels
     *
     * @var array
     */
    private $_traceLevels;

    /**
     * raw trace log
     *
     * @var array
     */
    private $_traceLog;

    /**
     * trace summery html
     *
     * @var string
     */
    private $_traceSummary;

    /**
     * each trace page
     *
     * @var array
     */
    private $_tracePage;

    /**
     * reflection log
     *
     * @var array
     */
    private $_ref;

    /**
     * Editor for file
     *
     * @var string
     */
    private $_editor = self::EDITOR_TEXTMATE;

    /**
     * Constructer
     */
    public function __construct()
    {
        $this->_init();
        $this->_run();
    }

    /**
     * Get trace depth
     *
     * @return integer
     */
    public function getLineNum()
    {
        return $this->_levelNum;
    }

    /**
     * Get trace page string
     *
     * @return string
     */
    public function getTracePage()
    {
        return $this->_tracePage;
    }

    /**
     * Get Summery page
     *
     * @return string
     */
    public function getSummeryPage()
    {
        return "<h3>Summery</h3>" . $this->_traceSummary;
    }

    /**
     * Get debug_backtrace() raw string html
     *
     * @return string
     */
    public function getRaw()
    {
        $raw = '<pre>' . print_r($this->_traceLog, true) . '</pre>';
        return $raw;
    }

    /**
     * Init
     *
     * @return void
     */
    private function _init()
    {
        // init
        $traceFile = Panda::getTempDir() . '/trace-' . $_GET['id'] . '.log';
        if (!file_exists($traceFile)) {
            error_reporting(E_ALL);
            trigger_error('invalid trace file. ' . $traceFile, E_USER_ERROR);
        }
        // store trace data
        $this->_traceLog = @unserialize(file_get_contents($traceFile));
        if (!$this->_traceLog) {
            die("<p>trace is not available...</p>");
        }
        // store refection data if exists
        $refDataFile = "{$traceFile}.ref.log";
        if (file_exists($refDataFile)) {
            $this->_ref = unserialize(file_get_contents($refDataFile));
        }
        $this->_traceLevels = array_keys($this->_traceLog);
        $this->_levelNum = count($this->_traceLevels);
        $this->_editor = isset($_GET['editor']) ? $_GET['editor'] : self::EDITOR_NONE;
    }

    /**
     * Create data as object property
     *
     * @return void
     */
    private function _run()
    {
        $i = 0;
        // make page data
        foreach ($this->_traceLevels as $level) {
            $trace = $this->_traceLog[$level];
            $line = isset($trace['line']) ? $trace['line'] : '';
            $file = isset($trace['file']) ? $trace['file'] : '';
            if (!file_exists($file)) {
                $hitLine = '';
                $shortList = '';
            } else {
                $files = file($trace['file']);
                $fileArray = array_map('htmlspecialchars', $files);
                if (isset($fileArray[$line - 1])) {
                    $fileArray[$line - 1] = "<span class=\"hit-line\">{$fileArray[$line - 1]}</span>";
                }
                $shortListArray = array_slice($fileArray, $line - 6, 11);
                if (isset($shortListArray[5])) {
                    $shortListArray[5] = "<a href=\"#traceline{$i}\" id=\"traceline-back{$i}\">{$shortListArray[5]}</a>";
                }
                $shortList = implode('', $shortListArray);
                $shortList = '<pre class="short-list">' . $shortList . '</pre>';
                if (isset($fileArray[$line - 1])) {
                    $hitLine = $fileArray[$line - 1];
                }
                if (isset($fileArray[$line - 1])) {
                    $fileArray[$line - 1] = "<a href=\"#traceline-back{$i}\" id=\"traceline{$i}\">{$fileArray[$line - 1]}</a>";
                }
            }
            $args = array();
            if (is_array($trace['args'])) {
                foreach ($trace['args'] as $arg) {
                    if (is_array($arg)) {
                        $args[] = 'Array';
                    } elseif (is_string($arg)) {
                        $args[] = "'{$arg}'";
                    } elseif (is_scalar($arg)) {
                        $args[] = $arg;
                    } else {
                        $args[] = 'Object';
                    }
                }
                $args = implode(',', $args);
            }
            if (isset($trace['class'])) {
                $hitInfo = "{$trace['class']}{$trace['type']}{$trace['function']}({$args}) ";
            } elseif (isset($trace['function'])) {
                $hitInfo = "{$trace['function']}({$args}) ";
            } else {
                $hitInfo = '';
            }
            $this->_traceSummary .= '<li><span class="timeline-num">' . $i . '</span>';
            $this->_traceSummary .= '<span class="timeline-body">' . $hitLine . '</span>';
            $this->_traceSummary .= '<span class="timeline-info">' . $hitInfo . '<br />';
            //            $this->_traceSummary .= '<span class="panda-file">';
            $this->_traceSummary .= $this->_getEditorLink($file, $line);
            // trace detail
            $this->_tracePage[$i] = "<h3 class='hit-head'>" . '<span class="timeline-num">' . $i . "</span>{$hitLine}</h3>";
            $this->_tracePage[$i] .= '<span id="hit-info">' . $hitInfo . '<br />';
            $this->_tracePage[$i] .= $this->_getEditorLink($file, $line) . '</span>';
            $this->_tracePage[$i] .= $shortList;
            if (isset($trace['args'])) {
                $this->_tracePage[$i] .= '<h3>Args</h3>';
                $this->_tracePage[$i] .= print_a($trace['args'], "return:1");
            }
            if (isset($trace['object'])) {
                $this->_tracePage[$i] .= '<h3>Object</h3>';
                $this->_tracePage[$i] .= print_a((array)$trace['object'], "return:1");
            }
            if (isset($this->_ref[$i]) && file_exists($this->_ref[$i]['file'])) {
                $array = array_slice(file($this->_ref[$i]['file']), $this->_ref[$i]['start'] - 1, $this->_ref[$i]['end'] - $this->_ref[$i]['start'] + 1);
                $methodCode = $this->_ref[$i]['doc'] . "\n";
                $methodCode .= implode('', $array);
                $open = "<?php ";
                $clode = " ?>";
                $code = highlight_string($open . $methodCode . $clode, true);
                $code = str_replace('&lt;?php', '', $code);
                $code = str_replace('?&gt;', '', $code);
                $code = str_replace('<span style="color: #0000BB">&lt;?php&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>', '', $code);
                $code = str_replace($clode, '', $code);
                $this->_tracePage[$i] .= "<h3>{$trace['class']}::{$trace['function']}()</h3>";
                $this->_tracePage[$i] .= '<pre><div class="short-list">' . $code . '</div></pre>';
                //                if (isset($this->_ref[$i]['export'])) {
            //                    $this->_tracePage[$i] .= '<h3>Class Reflection</h3>';
            //                    $this->_tracePage[$i] .= '<pre>' . $this->_ref[$i]['export'] . '</pre>';
            //                }
            }
            $i++;
        }
    }

    /**
     * Get editor link
     */
    private function _getEditorLink($file, $line = 0, $column = 0, $content = false)
    {
        $textmate = '<a title="Edit with TextMate" href="txmt://open/?url=file://';
        $textmate .= $file . '&line=' . $line . '&column=' . $column . '">' . '<img src="/__panda/image/textmate.png">' . '</a>';
        $bespin = '<a title="Edit with Bespin Editor" href="/__panda/edit/?file=';
        $bespin .= $file . '&line=' . $line . '&column=' . $column . '">' . '<img src="/__panda/image/bespin.png">' . '</a>';
        return "$file on line $line " . $textmate . $bespin;
    }
}
// retrieve valiables
$trace = new Panda_Trace_Page();
$levelNum = $trace->getLineNum();
$tracePage = $trace->getTracePage();
$summaryPage = $trace->getSummeryPage();
$raw = $trace->getRaw();
$path = (isset($_GET['path'])) ? $path : '/';
include './view.php';
?>
