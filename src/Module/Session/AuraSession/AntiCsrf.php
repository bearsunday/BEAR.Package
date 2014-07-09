<?php

namespace BEAR\Package\Module\Session\AuraSession;

use Aura\Input\AntiCsrfInterface;
use Aura\Input\Fieldset;
use Aura\Session\CsrfToken;

class AntiCsrf implements AntiCsrfInterface
{
    /**
     * @var \Aura\Session\CsrfToken
     */
    protected $csrf;

    /**
     * @param \Aura\Session\CsrfToken $csrf
     */
    public function __construct(CsrfToken $csrf)
    {
        $this->csrf = $csrf;
    }

    /**
     * adds a CSRF token field to the fieldset.
     *
     * @param \Aura\Input\Fieldset $fieldset
     */
    public function setField(Fieldset $fieldset)
    {
        $fieldset->setField('__csrf_token', 'hidden')
            ->setAttribs(['value' => $this->csrf->getValue()]);
    }

    /**
     * returns CSRF token is valid or not
     *
     * @param array $data
     * @return bool
     */
    public function isValid(array $data)
    {
        return isset($data['__csrf_token'])
            && $data['__csrf_token'] == $this->csrf->getValue();
    }
}
