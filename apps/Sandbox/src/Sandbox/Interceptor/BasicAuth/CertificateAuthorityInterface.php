<?php

namespace Sandbox\Interceptor\BasicAuth;

/**
 * CA interface
 */
interface CertificateAuthorityInterface
{

    /**
     * Authenticate
     *
     * @param string $user     user to authenticate.
     * @param string $password password to authenticate.
     *
     * @return boolean true if success authentication.
     */
    public function auth($user, $password);
}
