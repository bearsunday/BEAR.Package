{extends file="layout/demo.tpl"}
{block name=title}Index{/block}

{block name=page}
    <h2>BEAR.Demo</h2>
    <ul>
        <li>form</li>
        <ul>
            <li><a href="/blog/posts/newpost">plain form</a></li>
            <li><a href="form/auraform">aura form</a></li>
        </ul>
        <li>page</li>
        <ul>
            <li><a href="/hello/world?name=WORLD">hello world</a></li>
            <li><a href="page/redirect/">redirect</a></li>
            <li><a href="page/http/">http status code</a></li>
            <li><a href="page/hyperlink/">hyper link</a></li>
        </ul>
        <li>database</li>
        <ul>
            <li><a href="/blog/posts">select</a></li>
            <li><a href="/blog/posts/newpost">insert</a></li>
            <li><a href="/blog/posts/pager">pager</a></li>
        </ul>
        <li>resource object</li>
        <ul>
            <li><a href="resource/param/">signal parameter</a></li>
            <li><a href="resource/hypermedia/">hyper media client</a></li>
            <li><a href="resource/http/">http resource (single request)</a></li>
            <li><a href="resource/http/multi">http resource (multi request)</a></li>
        </ul>
        <li>aspect annotations</li>
        <ul>
            <li><a href="/demo/aspect/cache/">@Cache</a></li>
            <li><a href="/demo/aspect/time/">@Time</a></li>
            <li>@Db</li>
            <li>@Pager</li>
            <li>@Transaction</li>
            <li>@CacheUpdate</li>
        </ul>

        <li>error</li>
        <ul>
            <li><a href="error/e503">503</a></li>
            <li><a href="error/exception">exception</a></li>
        </ul>
        <li>development functions</li>
        <ul>
            <li><a href="func/edit">edit($file)</a></li>
            <li><a href="func/p">p($var)</a></li>
            <li><a href="func/e">e()</a></li>
            <li><a href="func/printo">print_o($var)</a></li>
        </ul>
    </ul>
{/block}
