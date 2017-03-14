[TWIG]<!DOCTYPE html>
<html lang="{{ lang['langcode'] }}">
<head>
	<meta charset="{{ lang['encoding'] }}" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="content-language" content="{{ lang['langcode'] }}" />
	<meta name="generator" content="{{ what }} {{ version }}" />
	<meta name="document-state" content="dynamic" />
	{{ htmlvars }}
	<title>{{ titles }}</title>
	<!-- Bootstrap Core CSS -->
	<link rel="stylesheet" href="{{ tpl_url }}/lib/bootstrap/css/bootstrap.min.css">
	<!-- Additional fonts for this theme -->
	<link rel="stylesheet" href="{{ tpl_url }}/lib/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300">
	<!-- Custom styles for this theme -->
	<link href="{{ tpl_url }}/css/clean-blog.css" rel="stylesheet">
	<!--[if lt IE 9]>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<!-- Temporary navbar container fix until Bootstrap 4 is patched -->
	<style>
	.navbar-toggler {
		z-index: 1;
	}
	
	@media (max-width: 576px) {
		nav > .container {
			width: 100%;
		}
	}
	</style>
	
	<!-- jQuery Version 3.1.1 -->
	<script src="{{ tpl_url }}/lib/jquery/jquery.js"></script>

	<!-- Tether -->
	<script src="{{ tpl_url }}/lib/tether/tether.min.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="{{ tpl_url }}/lib/bootstrap/js/bootstrap.min.js"></script>

	<!-- Theme JavaScript -->
	<script src="{{ tpl_url }}/js/clean-blog.js"></script>
	<script src="{{ scriptLibrary }}/functions.js"></script>
	<script src="{{ scriptLibrary }}/ajax.js"></script>
	{% if pluginIsActive('rss_export') %}<link href="{{ home }}/rss.xml" rel="alternate" type="application/rss+xml" title="RSS" />{% endif %}
</head>

<body>

	<!-- Navigation -->
	<nav class="navbar fixed-top navbar-toggleable-md navbar-light" id="mainNav">
		<div class="container">
			<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation"><i class="fa fa-bars"></i></button>
			<a class="navbar-brand page-scroll" href="{{ home }}">{{ home_title }}</a>
			<div class="collapse navbar-collapse" id="navbarResponsive">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item">
						<a class="nav-link page-scroll" href="{{ home }}">Home</a>
					</li>
					<li class="nav-item">
						<a class="nav-link page-scroll" href="about.html">About</a>
					</li>
					<li class="nav-item">
						<a class="nav-link page-scroll" href="post.html">Sample Post</a>
					</li>
					<li class="nav-item">
						<a class="nav-link page-scroll" href="contact.html">Contact</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	{% if isHandler('news:main|news:by.category|news:by.month|news:by.day') %}
	
	<!-- Page Header -->
		<header class="intro-header" style="background-image: url('{{ tpl_url }}/img/home-bg.jpg')">
			<div class="container">
				<div class="row">
					<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
						<div class="site-heading">
							<h1>{{ home_title }}</h1>
							<hr class="small">
							<span class="subheading">{{ lang.news }}</span>
						</div>
					</div>
				</div>
			</div>
		</header>
		<!-- Main Content -->
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
					{{ mainblock }}
				</div>
			</div>
		</div>
	{% else %}
		
		{{ mainblock }}
	{% endif %}

	<hr>

	<!-- Footer -->
	<footer>
		<div class="container">
			<div class="row">
				<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
					<ul class="list-inline text-center">
						<li class="list-inline-item">
							<a href="#">
								<span class="fa-stack fa-lg">
									<i class="fa fa-circle fa-stack-2x"></i>
									<i class="fa fa-twitter fa-stack-1x fa-inverse"></i>
								</span>
							</a>
						</li>
						<li class="list-inline-item">
							<a href="#">
								<span class="fa-stack fa-lg">
									<i class="fa fa-circle fa-stack-2x"></i>
									<i class="fa fa-facebook fa-stack-1x fa-inverse"></i>
								</span>
							</a>
						</li>
						<li class="list-inline-item">
							<a href="#">
								<span class="fa-stack fa-lg">
									<i class="fa fa-circle fa-stack-2x"></i>
									<i class="fa fa-github fa-stack-1x fa-inverse"></i>
								</span>
							</a>
						</li>
					</ul>
					<p class="copyright text-muted">Copyright &copy;  <a title="{{ home_title }}" href="{{ home }}">{{ home_title }}</a>. Powered by <a title="Next Generation CMS" target="_blank" href="http://ngcms.ru/">NG CMS</a> 2007 — {{ now|date("Y") }}. <br />{{ lang.sql_queries }}: <b>{{ queries }}</b> | {{ lang.page_generation }}: <b>{{ exectime }}</b> {{ lang.sec }} | <b>{{ memPeakUsage }}</b> Mb&nbsp;</p>
				</div>
			</div>
		</div>
	</footer>

	<div id="loading-layer" class="col-md-3"><i class="fa fa-spinner fa-pulse"></i> Пожалуйста, подождите . . .</div>

	<!-- jQuery Version 3.1.1 ->
	<script src="{{ tpl_url }}/lib/jquery/jquery.js"></script>

	<!-- Tether ->
	<script src="{{ tpl_url }}/lib/tether/tether.min.js"></script>

	<!-- Bootstrap Core JavaScript ->
	<script src="{{ tpl_url }}/lib/bootstrap/js/bootstrap.min.js"></script>

	<!-- Theme JavaScript ->
	<script src="{{ tpl_url }}/js/clean-blog.js"></script>
	<script src="{{ scriptLibrary }}/functions.js"></script>
	<script src="{{ scriptLibrary }}/ajax.js"></script-->

</body>
</html>
[debug]
{debug_queries}<br/>{debug_profiler}
[/debug]
[/TWIG]