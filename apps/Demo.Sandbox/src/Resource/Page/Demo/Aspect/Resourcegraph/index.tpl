{extends file="layout/demo.tpl"}
{block name=title}@ResourceGraph{/block}
{block name=page}

 <h2>{$greeting}</h2>
 <p>{$performance} / sec</p>
{/block}