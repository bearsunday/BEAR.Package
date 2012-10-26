<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>P(roject)Edit</title>

<script src="js/jquery-1.6.1.js" type="text/javascript" charset="utf-8"></script>
<script src="js/jquery.keybind/jquery.keybind.js" type="text/javascript" charset="utf-8"></script>
<script src="js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="js/ace/theme-eclipse.js" type="text/javascript" charset="utf-8"></script>
<script src="js/ace/mode-php.js" type="text/javascript" charset="utf-8"></script>
<script src="js/pandaEdit.js" type="text/javascript" charset="utf-8"></script>

<script src="jquery.easing.js" type="text/javascript"></script>
<script src="jqueryFileTree/jqueryFileTree.js" type="text/javascript"></script>
<link href="index.css" rel="stylesheet" type="text/css" media="screen" />

<link href="jqueryFileTree/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />
<link href="edit.css" media="screen" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="index.js"></script>
<?php $q = (isset($_GET['id'])) ? "?id={$_GET['id']}" : ''; ?>
<script type="text/javascript" src="init.js.php<?php echo $q?>"></script>

</script>
</head>
<body class="twoColHybLt" style="background-color:#DFE4EA">
<div style="background-color: #c9d9fb;" ><img src="jqueryFileTree/images/file.png" align="bottom"><span id="path" class="path"> Unloaded</span></div>
<div id="container">
<div id="sidebar1" style="overflow:scroll">
  <div id="container_id1"></div>
  <div id="container_id2"></div>
  <div id="container_id3"></div>
  <div id="container_id"></div>
</div>
  <div id="mainContent">
    <div id="editor" style="position:absolute; left:<?php echo (isset($_GET['x'])) ? $_GET['x'] : 200; ?>px; background-color:white; color:gray; width: 1000px; height: 95%; border: 1px solid black; "></div>
 <div id="file_info"></div>
    <div id="label" class="editor_label"><span class="editor_file_save">BEAR</span></div>
    </div>
</div>
</body>
</html>