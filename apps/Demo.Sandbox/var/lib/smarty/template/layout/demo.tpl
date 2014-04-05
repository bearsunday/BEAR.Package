<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>{block name=title}{/block} &laquo; Sandbox function demo</title>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
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