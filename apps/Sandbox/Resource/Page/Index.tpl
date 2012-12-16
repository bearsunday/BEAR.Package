<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>BEAR.Sunday</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BEAR, a resource oriented framework">
    <meta name="author" content="Akihito Koriyama">

    <!-- Le styles -->
    <link href="/assets/css/bootstrap.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Montserrat+Subrayada' rel='stylesheet' type='text/css'>
    <link href="/assets/css/bootstrap-responsive.css" rel="stylesheet">
    <style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;

        }

        .effect {
            color: white;
            text-shadow: 3px 3px 8px #BF7F00, -3px -3px 8px #BF7F00
        }

        .sub-title {
            font-family: "Montserrat Subrayada" sans-serif;
        }

        .hero-unit h1 {
            color: white;
        }
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="/assets/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="/assets/ico/apple-touch-icon-57-precomposed.png">
</head>

<body>

<!-- Navbar
================================================== -->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <i class="icon-bar"></i>
                <i class="icon-bar"></i>
                <i class="icon-bar"></i>
            </a>
            <a class="brand" href="#">BEAR.Sunday</a>

            <div class="nav-collapse collapse">
                <ul class="nav">
                    <li class="active"><a href="#">Home</a></li>
                    <li><a href="mailto:koriyama@bear-project.net">Contact</a></li>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
    </div>
</div>

<!-- Subhead
================================================== -->
<div class="container">

    <!-- Main hero unit for a primary marketing message or call to action -->
    <div class="hero-unit">
        <h1 class="effect">{$greeting}</h1>

        <p class="sub-title">A Resource Oriented Framework for PHP5.4
            <a href="http://travis-ci.org/koriym/Ray.Di"><img src="https://secure.travis-ci.org/koriym/Ray.Di.png"></a>
        </p>

        <p><a class="btn btn-primary btn-large" href="https://github.com/koriym/BEAR.Sunday">View project on
            GitHub &raquo;</a>
            <a rel="tooltip" title="{$apc.size} bytes of {$apc.total} user APC entries will be cleared."
               class="btn btn-primary btn-large btn btn-warning"
               href="/_dev/refresh.php"><i class="icon-refresh"></i> Refresh</a></p>
    </div>
    <!-- Example row of columns -->
    <div class="row">
        <div class="span4">
            <h2>Version</h2>
            <ul>
                <li>BEAR.Sunday <code>{$version.BEAR}</code></li>
                <li>PHP <code>{$version.php}</code></li>
            </ul>
            <h2>Extension</h2>

            <h3>required</h3>
            <ul>
                <li><a href="http://www.php.net/curl">curl</a> <code>{$extensions.curl}</code></li>
                <li><a href="http://www.php.net/apc">apc</a> <code>{$extensions.apc}</code></li>
            </ul>
            <h3>optional</h3>
            <ul>

                <li><a href="http://xdebug.org/">Xdebug</a> <code>{$extensions.Xdebug}</code></li>
                <li><a href="http://www.php.net/xhprof">xhprof</a> <code>{$extensions.xhprof}</code></li>
                <li><a href="http://www.php.net/memcache">memcache <code>{$extensions.memcache}</code></li>
                <li><a href="http://www.php.net/pdo_sqlite">pdo_sqlite</a> <code>{$extensions.pdo_sqlite}</code></li>
                <li><a href="http://www.php.net/mysqlnd">mysqlnd</a> <code>{$extensions.mysqlnd}</code></li>
            </ul>
        </div>
        <div class="span4">
            <h2>Techniques</h2>
            <ul>
                <li>Dependency Injection</li>
                <li>Aspect Oriented Design</li>
                <li>Representational State Transfer</li>
            </ul>
            <p><code>Ray.Di</code> - Guice style annotation-driven dependency injection framework <a
                    href="http://travis-ci.org/koriym/Ray.Di"><img
                    src="https://secure.travis-ci.org/koriym/Ray.Di.png"></a></p>

            <p><code>Ray.Aop</code> package provides method interception. This feature enables you to write code that is
                executed each time a matching method is invoked. <a
                        href="http://travis-ci.org/koriym/Ray.Aop"><img
                        src="https://secure.travis-ci.org/koriym/Ray.Aop.png"></a></p>

            <p><code>BEAR.Resource</code> - RESTful service layer framework. <a
                    href="http://travis-ci.org/koriym/BEAR.Resource"><img
                    src="https://secure.travis-ci.org/koriym/BEAR.Resource.png"></a></p>
        </div>
        <div class="span4">
            <h2>Sample apps</h2>
            <ul>
                <li><a href="{href rel="helloworld"}">Hello World</a></li>
                <li><a href="{href rel="blog"}">Blog tutorial</a></li>
                <li><a href="{href rel="restbucks"}">RESTBucks</a></li>
            </ul>
            <p><a class="btn" href="http://code.google.com/p/bearsunday/wiki/blog">Try tutorial &raquo;</a></p>

            <h2>Tools</h2>

            <p>
                <a class="btn" href="_dev/ide/index.php"
                   id="dev" rel="tooltip" title="Web IDE">Web IDE &raquo;</a>

            <p>
                <a class="btn btn-success" href="/_dev/apc.php?SCOPE=A&SORT1=H&SORT2=D&COUNT=20&OB=3&object_only"
                   id="apc" rel="tooltip" title="APC stored object">APC
                    Objects &raquo;</a>
            </p>
            <a class="btn" href="_dev/apc.php" id="apc" rel="tooltip" title="Open APC admin control panel">APC
                Admin &raquo;</a>
            </p>
            <p><a class="btn" href="_dev/memcache.php" id="memcache" rel="tooltip"
                  title="Open memcache admin carroll panel">Memcache Admin &raquo;</a>
            </p>

            <h2>Links</h2>
            <ul>
                <li><i class="icon-book"></i><a href="http://code.google.com/p/bearsunday/wiki/manual?tm=6">BEAR.Sunday
                    Manual</a></li>
                <li><i class="icon-book"></i><a href="http://code.google.com/p/rayphp/wiki/Motivation?tm=6">Ray.Di /
                    Ray.AOP Manual</a></li>
                <li><i class="icon-fire"></i><a href="https://github.com/koriym/BEAR.Sunday/issues">Issues</a></li>
                <li><i class="icon-wrench"></i><a href="_dev/index.html">Dev Tools</a></li>
        </div>
    </div>

    <hr>

    <footer>
        <p>&copy; 2012 <a href="https://twitter.com/#!/bearsunday">@BEARSunday</a> ({$performance} page / sec)</p>
        <p>Template Engine: Smarty</p>
    </footer>

</div>
<!-- /container -->
<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="/assets/js/jquery.js"></script>
<script src="/assets/js/bootstrap-tooltip.js"></script>
</body>
</html>
