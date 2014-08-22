<?php

// a session handler that does nothing, for testing purposes only

/**
 * Class FakeSessionHandler
 *
 * This class is taken from Aura.Session
 * @link https://github.com/auraphp/Aura.Session/blob/develop-2/tests/src/FakeSessionHandler.php
 */
class FakeSessionHandler
{
    public $data;

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        $this->data = null;
        return true;
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function open($save_path, $session_id)
    {
        return true;
    }

    public function read($session_id)
    {
        return $this->data;
    }

    public function write($session_id, $session_data)
    {
        $this->data = $session_data;
    }
}

$handler = new FakeSessionHandler();
session_set_save_handler(
    array($handler, 'open'),
    array($handler, 'close'),
    array($handler, 'read'),
    array($handler, 'write'),
    array($handler, 'destroy'),
    array($handler, 'gc')
);

// avoid session start warning
session_start();
