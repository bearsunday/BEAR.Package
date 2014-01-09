{extends file="layout/demo.tpl"}
{block name=title}@Cache{/block}

{block name=page}
<h1>{$num}</h1>
    <p>* make sure "clear-cache" is disabled in <a href="/dev/edit/?file=/apps/Demo.Sandbox/public/web.php">web.php</a> script.</p>
{/block}
