{extends file="layout/demo.tpl"}
{block name=title}Page link{/block}

{block name=page}
    <h3>User [{$user_id}]</h3>
    <ul>
        <li><a href="{href rel="profile"}">check your profile id</a></li>
        <li><a href="{href rel="help"}">need help ?</a></li>
    </ul>
    <p>ページのリンクにURIテンプレートを使うことができます</p>
{/block}
