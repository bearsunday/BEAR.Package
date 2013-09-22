<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>{block name=title}{/block} &laquo; Sandbox function demo</title>
    <link href="/assets/css/bootstrap.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-responsive.css" rel="stylesheet">
    <script src="/assets/js/jquery.js"></script>
</head>
<body>

<ul class="breadcrumb">
    <li><a href="/">Home</a> <span class="divider">/</span></li>
    <li><a href="/demo/">Demo</a></li><span class="divider">/</span>
    <li class="active">{block name=title}{/block}</li>
</ul>


<div class="container">{block name=page}{/block}</div>
</body>
</html>