{extends file="layout/demo.tpl"}
{block name=title}Hypermedia Client{/block}

{block name=page}
    <p>Payment URI: <strong>{$payment_uri}</strong></p>
    <p>Result: <strong>{$payment->code}</strong></p>
    See also
    <ul>
        <li><a href="/dev/edit/index.php?file=/apps/Demo.Sandbox/Resource/App/Demo.Hypermedia/Order.php">app://self/demo/hypermedia/order</a></li>
        <li><a href="/dev/edit/index.php?file=/apps/Demo.Sandbox/Resource/App/Demo.Hypermedia/Payment.php">app://self/demo/hypermedia/payment</a></li>
    </ul>
{/block}
