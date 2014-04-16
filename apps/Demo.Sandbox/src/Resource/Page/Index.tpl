<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BEAR.Sunday Demo\Sandbox application">
    <meta name="author" content="Akihito Koriyama">
    <link rel="shortcut icon" href="/assets/ico/favicon.png">

    <title>BEAR.Sunday</title>
    <!-- Bootstrap core CSS -->
    <link href="//netdna.bootstrapcdn.com/bootswatch/3.1.1/united/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<!-- Main jumbotron for a primary marketing message or call to action -->
<div class="jumbotron">
    <div class="container">
        <h1 class="effect">{$greeting}</h1>

        <p class="sub-title">
            BEAR.Sunday Demo\Sandbox application
        </p>
    </div>
</div>

<div class="container">
    <!-- Example row of columns -->
    <div class="row">
        <div class="col-lg-4">
            <h2>Version</h2>
            <ul>
                <li>BEAR.Sunday <code>{$version.BEAR}</code></li>
                <li>Latest Stable <a href="https://packagist.org/packages/bear/package"><img
                                src="https://poser.pugx.org/bear/package/v/stable.png"></a></li>
                <li>PHP <code>{$version.php}</code></li>
            </ul>
            <h2>Packages</h2>

            <p>
                <a href="https://github.com/koriym/Ray.Di"><code>Ray.Di</code></a>
                <a href="http://travis-ci.org/koriym/Ray.Di">
                    <img src="https://secure.travis-ci.org/koriym/Ray.Di.png?branch=master">
                </a>
            </p>

            <p>
                <a href="https://github.com/koriym/Ray.Aop"><code>Ray.Aop</code></a>
                <a href="http://travis-ci.org/koriym/Ray.Aop">
                    <img src="https://secure.travis-ci.org/koriym/Ray.Aop.png?branch=master">
                </a>
            </p>

            <p>
                <a href="https://github.com/koriym/BEAR.Resource"><code>BEAR.Resource</code></a>
                <a href="http://travis-ci.org/koriym/BEAR.Resource">
                    <img src="https://secure.travis-ci.org/koriym/BEAR.Resource.png?branch=master">
                </a>
            </p>

            <p>
                <a href="https://github.com/koriym/BEAR.Sunday"><code>BEAR.Sunday</code></a>
                <a href="http://travis-ci.org/koriym/BEAR.Sunday">
                    <img src="https://secure.travis-ci.org/koriym/BEAR.Sunday.png?branch=master">
                </a>
            </p>
            <p>
                <a href="https://github.com/koriym/BEAR.Package"><code>BEAR.Package</code></a>
                <a href="http://travis-ci.org/koriym/BEAR.Package">
                    <img src="https://secure.travis-ci.org/koriym/BEAR.Package.png?branch=master">
                </a>
            </p>
        </div>
        <div class="col-lg-4">
            <h2>Applications</h2>
            <ul>
                <li><a href="{href rel="helloworld"}">Hello World</a></li>
                <li><a href="{href rel="blog"}">Blog tutorial</a></li>
                <li><a href="{href rel="restbucks"}">RESTBucks</a></li>
                {if $is_cli_server}
                    <li><a href="{href rel="demo"}">BEAR.Demo</a></li>
                {/if}
            </ul>
            <h2>Development</h2>
                <a href="/dev"><button class="btn btn-danger">/dev</button></a>
        </div>
    </div>

    <hr>

    <footer>
        <p>&copy; 2013 <a href="https://twitter.com/#!/bearsunday">@BEARSunday</a> ({$performance} page / sec)</p>
        <p>template engine: Smarty</p>
    </footer>
</div>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</body>
</html>
