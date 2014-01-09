{extends file="layout/demo.tpl"}
{block name=title}HTTP Resource (Multi){/block}

{block name=page}
    at once:
        <ul>
            <li>http://news.google.com/news?hl=ja&ned=us&ie=UTF-8&oe=UTF-8&output=rss</li>
            <li>http://phpspot.org/blog/index.xml</li>
            <li>http://rss.excite.co.jp/rss/excite/odd</li>
        </ul>
    <pre>{$xml}</pre>
{/block}
