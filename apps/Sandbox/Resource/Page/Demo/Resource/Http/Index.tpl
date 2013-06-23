{extends file="layout/demo.tpl"}
{block name=title}HTTP Resource{/block}

{block name=page}
    http://www.feedforall.com/sample.xml
    <pre>{$xml}</pre>
{/block}
