<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>BEAR IDE</title>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <script src="http://d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js"></script>
    <script src="../edit/codeEdit.js" type="text/javascript" charset="utf-8"></script>
    <script src="jquery.easing.js" type="text/javascript"></script>
    <script src="jqueryFileTree/jqueryFileTree.js" type="text/javascript"></script>
    <script src="index.js" type="text/javascript"></script>
    <script src="init.js.php" type="text/javascript"></script>

    <link href="index.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="jqueryFileTree/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="edit.css" media="screen" rel="stylesheet" type="text/css"/>
</head>
<body class="twoColHybLt" style="background-color:#DFE4EA">
<div style="background-color: #c9d9fb;">
    <img src="jqueryFileTree/images/file.png" align="bottom">
    <span id="path" class="path">n/a</span>
</div>
<div id="container">
    <div id="sidebar1" style="overflow:scroll">
        <div id="container_id"></div>
    </div>
    <div id="mainContent">
        <div id="editor"
             style="position:absolute; left: 200px; ?>px; background-color:white; color:gray; width: 2000px; height: 95%; border: 1px solid black; "></div>
        <div id="file_info"></div>
        <div id="label" class="editor_label"><span class="editor_file_save" id="save_now"> n/a </span></div>
</body>
</html>