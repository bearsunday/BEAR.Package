<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Resource;

use Aura\Sql\ExtendedPdo;

class ResourceLog
{
    const TABLE_CLOSE = '</table></div>';

    /**
     * @var string
     */
    private $file;

    /**
     * @param $file string
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function toTable()
    {
        $pdo = new ExtendedPdo("sqlite:{$this->file}");
        try {
            $pages = $pdo->fetchAll('SELECT DISTINCT extra_page FROM log ORDER BY timestamp DESC');
        } catch (\PDOException $e) {
            return '';
        }
        $html = '';
        foreach ($pages as $page) {
            $page = each($page)['value'];
            $logs = $this->getPageLog($pdo, $page);
            $html .= $this->getTables($logs);
        }

        return $html;
    }

    /**
     * Return resource log
     *
     * @param ExtendedPdo $db
     * @param string      $page
     *
     * @return array
     */
    private function getPageLog(ExtendedPdo $db, $page)
    {
        $result = $db->fetchAll(
            'SELECT * FROM `log` WHERE `extra_page` = ' . "'{$page}' ORDER BY id ASC"
        );
        $logs = [];
        $log = '';
        foreach ($result as $row) {
            $keyValues = explode("\t", $row['message']);
            foreach ($keyValues as $keyValue) {
                $pos = strpos($keyValue, ':');
                $key = substr($keyValue, 0, $pos);
                $value = substr($keyValue, $pos + 1);
                $log[$key] = $value;
            }
            $log['timestamp'] = (new \DateTime($row['timestamp']))->format('H:i:s');
            unset($row['message']);
            $logs[] = $log;
        }
        return $logs;
    }

    /**
     * @param mixed $logs array | arrayAccess
     *
     * @return string
     */
    private function getTables(array $logs)
    {
        $tableBody = '';
        foreach ($logs as $log) {
            $code = $this->getCode($log['code']);
            $body = print_a(json_decode($log['body'], true), "return:1");
            $meta = $this->getHeaderInfo($log['req'], $log['header']);
            if ($meta) {
                $body .= '<i class="icon-info-sign"></i><br>' . print_a($meta, "return:1");
            }
            $tableBody .= <<<EOT
                <tr>
                    <td width="30">$code</td>
                    <td><tt>{$log['req']}</tt></td>
                </tr>
                <tr>
                    <td></td>
                    <td>$body</td>
                </tr>
EOT;
        }
        $path = isset($log['path']) ? $log['path'] : '';
        $body = $this->getTableOpen($path, $logs) . $tableBody . self::TABLE_CLOSE;

        return $body;
    }

    /**
     * @param int $code
     *
     * @return string
     */
    private function getCode($code)
    {
        $status = substr($code, 0, 1);
        $label = ($status == 2) ? 'success' : 'error';

        return "<span class=\"label label-{$label}\">{$code}</code>";
    }

    /**
     * @param string $path
     * @param array  $logs
     *
     * @return string
     */
    private function getTableOpen($path = '', array $logs = [])
    {
        $time = $logs[0]['timestamp'];
        $requestNum = count($logs);
        $tableOpen = <<<EOT
  <div class="well">
  <div align="right"><i class="icon-time" style="margin-left:auto;"></i> {$time} <i class="icon-leaf" title="number of request(s)"></i> {$requestNum}</div>
  <span class="label">{$path}</span>
  <table class="table table-hover table-condensed">
    <thead>
    <tr>
        <th>Status</th>
        <th>Request</th>
    </tr>
    </thead>
EOT;

        return $tableOpen;
    }

    /**
     * Place meta info to header from log
     *
     * @param string $req
     * @param string $header
     *
     * @return array
     */
    private function getHeaderInfo($req, $header)
    {
        $meta = [];

        $method = substr($req, 0, 3);
        $onMethod = 'on' . ucwords($method);
        $header = json_decode($header, true);

        // interceptors
        if (isset($header['x-interceptors'])) {
            $xInterceptors = is_array($header['x-interceptors']) ? $header['x-interceptors'][0] : $header['x-interceptors'];
            $interceptors = json_decode($xInterceptors);
            if (isset($interceptors->$onMethod)) {
                $appliedInterceptors = $interceptors->$onMethod;
                $meta['interceptor'] = $appliedInterceptors;
            }
        }

        // cache
        if (isset($header['x-cache'])) {
            $meta['@Cache'] = json_decode($header['x-cache']);
        }

        // cache
        if (isset($header['x-sql'])) {
            $meta['SQL'] = $header['x-sql'];
        }

        return $meta;
    }
}
