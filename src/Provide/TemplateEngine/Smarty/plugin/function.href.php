<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
use BEAR\Resource\Link;

/**
 * Smarty plugin
 */

/**
 * Smarty {href} function plugin
 *
 * Type:     function<br>
 * Name:     href<br>
 * Purpose:  make uri link<br>
 * Params:
 *
 * <pre>
 * - rel        - (required) - relation
 * - data       - (required) - data for template
 * </pre>
 *
 * Examples:
 * <pre>
 * {href rel="blog}
 * {href rel="entry" data=$resource->body}
 * </pre>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @param array                    $params   parameters
 * @param Smarty_Internal_Template $template template object
 *
 * @return string
 */
function smarty_function_href($params, $template)
{
    if (empty($params['rel'])) {
        trigger_error("href: missing 'rel' parameter", E_USER_WARNING);

        return '';
    }
    if (empty($template->smarty->tpl_vars['resource']->value->links[$params['rel']])) {
        trigger_error("href: links needs {$params['rel']} parameter", E_USER_WARNING);

        return '';
    }

    $rel = $params['rel'];
    $data = (isset($params['data'])) ? $params['data'] : $template->smarty->tpl_vars['resource']->value->body;
    $resource = $template->smarty->tpl_vars['resource']->value;
    $link = $resource->links[$rel];
    $uri = (isset($link[Link::TEMPLATED]) && $link[Link::TEMPLATED] === true) ?
        \GuzzleHttp\uri_template($link[Link::HREF], (array) $data) :
        $link[Link::HREF];

    // remove "page://self/"
    $uri = str_replace('page://self/', '/', $uri);

    return $uri;
}
