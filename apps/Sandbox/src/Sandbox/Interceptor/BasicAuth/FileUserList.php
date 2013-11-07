<?php

namespace Sandbox\Interceptor\BasicAuth;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * File based user list class
 */
class FileUserList implements CertificateAuthorityInterface
{
    /**
     * Web context provider
     *
     * @var WebContextProvider
     */
    private $webContextProvider;

    /**
     * Constructor
     *
     * @Inject
     * @Named("basic_pass_file")
     *
     * @param string $pass password file path
     */
    public function __construct($pass)
    {
        $this->basicPassFile = $pass;
    }

    /**
     * {@inheritdoc}
     */
    public function auth($user, $password)
    {
        if (file_exists($this->basicPassFile) === false) {
            return false;
        }
        $userList = file($this->basicPassFile);
        foreach ($userList as $data) {
            list($listedUser, $listedHash) = explode(':', trim($data));
            if ($listedUser !== $user) {
                continue;
            }

            return $this->verify($password, $listedHash);
        }

        return false;
    }

    /**
     * verify password
     *
     * @param string $password user passoword
     * @param string $hash     hashed password
     */
    private function verify($password, $hash)
    {
        if (strpos($hash, '$apr1$') === 0) {
            // APR1-MD5
            $salt = substr($hash, 6, 8);
            $encryptedPass = $this->cryptApr1Md5($password, $salt);
        } else {
            $encryptedPass = crypt($password, $hash);
        }

        return $hash === $encryptedPass;
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
