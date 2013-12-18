<?php
/**
 * Hello World - Pull
 *
 * Minimum application using pull request.
 */
$app = require dirname(dirname(__DIR__)) . '/scripts/instance.php';

$hello = $app->resource
             ->get
             ->uri('app://self/hello')
             ->withQuery(['name' => 'Pull world !'])
             ->eager
             ->request();
?>
<html>
<body>
greeting: <?php echo $hello['greeting']; ?>
time: <?php echo $hello['time']; ?>
</body>
</html>
