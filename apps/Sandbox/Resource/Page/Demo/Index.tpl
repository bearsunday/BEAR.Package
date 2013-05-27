{extends file="layout/demo.tpl"}
{block name=title}Index{/block}

{block name=page}
<h2>DEMO</h2>

<h3>example</h3>
  <ul>
    <li><a href="redirect">redirect</a></li>
    <li><a href="param">signal Parameter</a></li>
    <li><a href="form/auraform">form (Aura.Input)</a></li>
  </ul>

<h3>dev function</h3>
  <ul>
    <li><a href="func/edit">edit($file)</a></li>
    <li><a href="func/p">p($var)</a></li>
    <li><a href="func/e">e()</a></li>
    <li><a href="func/printo">print_o($var)</a></li>
  </ul>

<h3>error</h3>
  <ul>
    <li><a href="error/e503">503</a></li>
    <li><a href="error/exception">exception</a><p>(try in Production mode)</p></li>
  </ul>
{/block}
