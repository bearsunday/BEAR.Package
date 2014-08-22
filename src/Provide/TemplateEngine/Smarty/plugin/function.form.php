<?php

use Aura\Html\HelperLocatorFactory;

/**
 * Aura.Input form helper
 *
 * @param $params
 * @param $smarty
 *
 * @return mixed
 */
function smarty_function_form($params, &$smarty)
{
    static $formHelper;

    if (!$formHelper) {
        $factory = new HelperLocatorFactory();
        $formHelper = $factory->newInstance();
    }

    $formElementHtml = $formHelper->input($params['hint']);

    return $formElementHtml;
}
