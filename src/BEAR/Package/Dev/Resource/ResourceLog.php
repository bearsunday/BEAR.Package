<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Resource;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

/**
 * Resource log db
 */
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
        $dbConfig = [
            'driver' => 'Pdo_Sqlite',
            'dsn' => 'sqlite:' . $this->file
        ];
        $db = new Adapter($dbConfig);
        try {
            $pages = $db->query(
                'SELECT DISTINCT extra_page FROM log ORDER BY timestamp DESC',
                Adapter::QUERY_MODE_EXECUTE
            )->toArray();
        } catch (\PDOException $e) {
            return '';
        }
        $html = '';
        foreach ($pages as $page) {
            $page = each($page)['value'];
            $logs = $this->getPageLog($db, $page);
            $html .= $this->getTables($logs);
        }

        return $html;
    }

    /**
     * Return resource log
     *
     * @param \Zend\Db\Adapter\Adapter $db
     * @param                          $page
     *
     * @return array
     */
    private function getPageLog(Adapter $db, $page)
    {
        $result = $db->query(
            'SELECT * FROM `log` WHERE `extra_page` = ' . "'{$page}' ORDER BY id ASC",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();
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
     * @param $logs
     *
     * @return string
     */
    private function getTables($logs)
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
                    <td width="80"><tt>{$log['timestamp']}</tt></td>
                    <td width="30">$code</td>
                    <td width="300"><tt>{$log['req']}</tt></td>
                    <td>$body</td>
                </tr>
EOT;
        }
        $path = isset($log['path']) ? $log['path'] : '';
        $body = $this->getTableOpen($path) . $tableBody . self::TABLE_CLOSE;

        return $body;
    }

    /**
     * @param $code
     *
     * @return string
     */
    private function getCode($code)
    {
        $status = substr($code, 0, 1);
        if ($status == 2) {
            $label = 'success';
        } else {
            $label = 'error';
        }

        return "<span class=\"label label-{$label}\">{$code}</code>";
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getTableOpen($path = '')
    {
        $tableOpen = <<<EOT
<div class="well">
  <span class="label">{$path}</span>
  <table class="table table-hover table-condensed">
    <thead>
    <tr>
        <th>Time</th>
        <th>Status</th>
        <th>Request</th>
        <th>Result</th>
    </tr>
    </thead>
EOT;

        return $tableOpen;
    }

    private function getHeaderInfo($req, $header)
    {
        $meta = [];

        $method = substr($req, 0 , 3);
        $onMethod = 'on' . ucwords($method);
        $header = json_decode($header, true);

        // interceptors
        if (isset($header['x-interceptors'])) {
            $interceptors = json_decode($header['x-interceptors']);
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
