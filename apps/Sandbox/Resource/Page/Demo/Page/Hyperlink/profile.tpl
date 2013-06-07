{extends file="layout/demo.tpl"}
{block name=title}Page link{/block}

{block name=page}

<p>Your profile id is [{$id}]</p>

<a href="{href rel="back"}">back</a>
</ul>
{/block}
