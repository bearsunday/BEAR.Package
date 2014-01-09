<?php
use Aura\View\Helper\Form\Field;
use Aura\View\Helper\Form\Input;
use Aura\View\Helper\Form\Input\Checked;
use Aura\View\Helper\Form\Radios;
use Aura\View\Helper\Form\Repeat;
use Aura\View\Helper\Form\Select;
use Aura\View\Helper\Form\Textarea;
use Aura\View\HelperLocator;

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
        $packageDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        $formHelper = new HelperLocator(
            [
                'field' => function () use ($packageDir) {
                    return new Field(require $packageDir . '/vendor/aura/view/scripts/field_registry.php');
                },
                'input' => function () use ($packageDir) {
                    return new Input(require $packageDir . '/vendor/aura/view/scripts/input_registry.php');
                },
                'radios' => function () {
                    return new Radios(new Checked);
                },
                'repeat' => function () use ($packageDir) {
                    return new Repeat(require $packageDir . '/vendor/aura/view/scripts/repeat_registry.php');
                },
                'select' => function () {
                    return new Select;
                },
                'textarea' => function () {
                    return new Textarea;
                },
            ]
        );
    }
    $type = isset($params['type']) ? $params['type'] : 'field';
    $func = $formHelper->get($type);
    $formElementHtml = $func($params['name']);

    return $formElementHtml;
}
