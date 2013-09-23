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
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5);
        }

        .sub-title {
        }

        .hero-unit h1 {
        }
    </style>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
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

        <p class="sub-title">
        <a href="http://travis-ci.org/koriym/Ray.Di"><img src="https://secure.travis-ci.org/koriym/Ray.Di.png"></a>
        A Resource Oriented Framework for PHP5.4 - v0.9
        </p>

        <p><a class="btn btn-primary btn-large" href="https://github.com/koriym/BEAR.Sunday">View project on
            GitHub &raquo;</a>
    </div>
    <!-- Example row of columns -->
    <div class="row">
        <div class="span4">
            <h2>Version</h2>
            <ul>
                <li>BEAR.Sunday <code>{$version.BEAR}</code></li>
                <li>Latest Stable <a href="https://packagist.org/packages/bear/package"><img src="https://poser.pugx.org/bear/package/v/stable.png"></a></li>
                <li>PHP <code>{$version.php}</code></li>
            </ul>
        </div>
        <div class="span4">
            <h2>Frameworks</h2>
            <p><a href="https://github.com/koriym/Ray.Di"><code>Ray.Di</code></a>  - Guice style annotation-driven <strong>dependency injection framework</strong>  enables to build an object graph.<a
                    href="http://travis-ci.org/koriym/Ray.Di"><img
                    src="https://secure.travis-ci.org/koriym/Ray.Di.png"></a></p>

            <p><a href="https://github.com/koriym/Ray.Aop"><code>Ray.Aop</code></a> an<strong>aspect oriented framework</strong>,  Its feature enables you to write code that is
                executed each time a matching method is invoked. <a
                        href="http://travis-ci.org/koriym/Ray.Aop"><img
                        src="https://secure.travis-ci.org/koriym/Ray.Aop.png"></a></p>

            <p><a href="https://github.com/koriym/BEAR.Resource"><code>BEAR.Resource</code></a> - a <strong>hypermedia framework</strong> for object as a service that allows resources to behave as objects. <a
                    href="http://travis-ci.org/koriym/BEAR.Resource"><img
                    src="https://secure.travis-ci.org/koriym/BEAR.Resource.png"></a></p>
        </div>
        <div class="span4">
            <h2>Applications</h2>
            <ul>
                <li><a href="{href rel="helloworld"}">Hello World</a></li>
                <li><a href="{href rel="blog"}">Blog tutorial</a></li>
                <li><a href="{href rel="restbucks"}">RESTBucks</a></li>
                {if $is_cli_server}
                <li><a href="{href rel="demo"}">BEAR.Demo</a></li>
                {/if}
            </ul>

            <h2>Links</h2>
            <ul>
                <li><i class="icon-book"></i><a href="http://code.google.com/p/bearsunday/wiki/manual?tm=6">BEAR.Sunday
                    Manual</a></li>
                <li><i class="icon-book"></i><a href="http://code.google.com/p/rayphp/wiki/Motivation?tm=6">Ray.Di /
                    Ray.AOP Manual</a></li>
                <li><i class="icon-fire"></i><a href="https://github.com/koriym/BEAR.Package/issues">Issues</a></li>
        </div>
    </div>

    <hr>

    <footer>
        <p>&copy; 2012 <a href="https://twitter.com/#!/bearsunday">@BEARSunday</a> ({$performance} page / sec)</p>
        <p>template engine: Smarty</p>
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
