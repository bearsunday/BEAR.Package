{extends file="layout/demo.tpl"}
{block name=title}Signal Parameter{/block}

{block name=page}

<h2>DEMOs</h2>
<ul>
    <li><a href="redirect">Redirect</a></li>
    <li><a href="param">Signal Parameter</a></li>
</ul>

<h3>Error</h3>
<ul>
    <li><a href="error/e503">503</a></li>
    <li><a href="error/exception">Exception</a></li>
    <p>(try in Production mode)</p>
</ul>
{/block}
