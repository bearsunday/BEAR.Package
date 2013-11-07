<?php

namespace Sandbox\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use BEAR\Resource\ResourceObject;

/**
 * Basic Auth interceptor
 */
class BasicAuthInterceptor implements MethodInterceptor
{
    /**
     * Passowrd file path
     *
     * @var string
     */
    private $basicPassFile;

    /**
     * Constructor
     *
     * @Inject
     * @Named("basic_pass_file")
     */
    public function __construct($pass)
    {
        $this->basicPassFile = $pass;
    }

    /**
     * @param MethodInvocation $invocation Method Invocation
     */
    public function invoke(MethodInvocation $invocation)
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Please Enter Your Password."');
            header('HTTP/1.0 401 Unauthorized');
            header('Content-type: text/html; charset=UTF-8');

            die('Authentication Failed.');
        }
        $user = $_SERVER['PHP_AUTH_USER'];
        $passwd = $_SERVER['PHP_AUTH_PW'];
        $auth = $this->checkPassword($user, $passwd);
        if ($auth) {
            return $invocation->proceed();
        }

        die('Authentication Failed.');
    }

    /**
     * Basic認証パスワードチェック
     *
     * @link http://php.net/manual/ja/function.crypt.php
     *
     * @param string $user     ユーザー名
     * @param string $pass     パスワード
     *
     * @return boolean パスワードチェック成否
     */
    private function checkPassword($user, $pass)
    {
        if (file_exists($this->basicPassFile) === false) {
            return false;
        }
        $userList = file($this->basicPassFile);
        foreach ($userList as $data) {
            $record = explode(':', trim($data));
            if (!$record[0] === $user) {
                continue;
            }

            if (strpos($record[1], '$apr1$') === 0) {
                // APR1-MD5
                $salt = substr($record[1], 6, 8);
                $md5Pass = $this->cryptApr1Md5($pass, $salt);
                if ($record[1] === $md5Pass) {
                    return true;
                }

                return false;
            }

            $encryptedPass = crypt($pass);
            if ($record[1] === $encryptedPass) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * APR1方式のMD5文字列生成
     *
     * @param string $plainpasswd 暗号化してないパスワード文字列
     * @param string $salt salt
     *
     * @return string 暗号化後文字列
     */
    private function cryptApr1Md5($plainpasswd, $salt)
    {
        $len = strlen($plainpasswd);
        $text = $plainpasswd . '$apr1$' . $salt;
        $bin = pack("H32", md5($plainpasswd . $salt . $plainpasswd));
        for ($i = $len; $i > 0; $i -= 16) {
            $text .= substr($bin, 0, min(16, $i));
        }
        for ($i = $len; $i > 0; $i >>= 1) {
            $text .= ($i & 1) ? chr(0) : $plainpasswd{0};
        }
        $bin = pack("H32", md5($text));
        for ($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $plainpasswd : $bin;
            if ($i % 3) {
                $new .= $salt;
            }

            if ($i % 7) {
                $new .= $plainpasswd;
            }

            $new .= ($i & 1) ? $bin : $plainpasswd;
            $bin = pack("H32", md5($new));
        }

        $tmp = '';
        for ($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j == 16) {
                $j = 5;
            }
            $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
        }
        $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
        $tmp = strtr(
            strrev(substr(base64_encode($tmp), 2)),
            "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
            "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
        );

        return '$apr1$' . $salt . '$' . $tmp;
    }
}
