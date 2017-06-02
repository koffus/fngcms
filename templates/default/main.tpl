[TWIG]<!DOCTYPE html>
<html lang="{{ lang['langcode'] }}">
<head>
	<title>{{ titles }}</title>
	<meta charset="{{ lang['encoding'] }}" />
	<meta http-equiv="content-language" content="{{ lang['langcode'] }}" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="generator" content="{{ what }} {{ version }}" />
	<meta name="document-state" content="dynamic" />
	{{ htmlvars }}
	<!-- Bootstrap Core CSS -->
	<link href="{{ tpl_url }}/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<!-- Additional fonts for this theme -->
	<!--link href="https://fonts.googleapis.com/css?family=Roboto:300" rel="stylesheet" /-->
	<link href="{{ tpl_url }}/font-awesome/css/font-awesome.min.css" rel="stylesheet">
	<!-- Custom styles for this theme -->
	<link href="{{ tpl_url }}/css/style.css" rel="stylesheet">
	<!--[if lt IE 9]>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<!-- jQuery first, then Tether, then Bootstrap JS. -->
	<script src="{{ scriptLibrary }}/jq/jquery.min.js"></script>
	<script src="{{ tpl_url }}/bootstrap/js/tether.min.js"></script>
	<script src="{{ tpl_url }}/bootstrap/js/bootstrap.min.js"></script>
	<!-- Theme JavaScript -->
	<script src="{{ tpl_url }}/js/script.js"></script>
	<script src="{{ scriptLibrary }}/functions.js"></script>
	<script src="{{ scriptLibrary }}/ajax.js"></script>
	{% if pluginIsActive('rss_export') %}<link href="{{ home }}/rss.xml" rel="alternate" type="application/rss+xml" title="RSS" />{% endif %}
</head>
<body>

	<header>
		<div class="header-top navbar-inverse bg-inverse">
			<div class="container">
				<div class="row">
					<div class="col-6 text-left">
						<a href="mailto:info@site.com" class="nav-link"><span class="fa fa-envelope"></span> info@site.com</a>
					</div>
					<div class="col-6 text-right">
						<a href="tel:88001234567" class="nav-link"><span class="fa fa-phone-square"></span> 8 (800) 123-45-67</a>
					</div>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="row card-block align-items-center">
				<div class="col-6">
					<h1 class="site-title-heading">
						<a href="{{ home }}" title="{{ home_title }}" rel="home">{{ home_title }}</a>
					</h1>
					<p class="site-description">
						Lorem ipsum dolor sit amet
					</p>
				</div>
				<div class="col-6">
					{{ search_form }}
				</div>
			</div>
		</div>

		<div class="container">
			<div class="row"><div class="col-md-12">
			
			<nav id="mainNav" class="navbar navbar-toggleable-md  navbar-inverse bg-inverse">
				<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarCollapse"><span class="navbar-toggler-icon"></span></button>
				<a class="navbar-brand" href="{{ home }}" title="{{ home_title }}" rel="home">{{ home_title }}</a>
				<div class="collapse navbar-collapse justify-content-end" id="navbarCollapse">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item dropdown">
							<a href="{{ home }}#" class="nav-link dropdown-toggle" data-toggle="dropdown">{{ lang['news'] }} </a>
							<div class="dropdown-menu">
								{{ categories }}
							</div>
						</li>
						<li class="nav-item"><a href="{{ home }}/plugin/forum/" class="nav-link">{{ lang.theme.forum }}</a></li>
						<li class="nav-item"><a href="#" class="nav-link">{{ lang.theme.article }}</a></li>
						<li class="nav-item"><a href="{{ home }}/static/about.html" class="nav-link">{{ lang.theme.about }}</a></li>
					</ul>
					{{ personal_menu }}
				</div>
			</nav></div></div>
		</div>
	</header>

	{% if isHandler('news:main') and not(handler.params.page) %}
		{% include 'main.promo.tpl' %}
	{% else %}
		{% include 'main.page.tpl' %}
	{% endif %}

	<footer class="footer section">
		<div class="container">
			<div class="row mb-5 text-center text-md-left">
				<div class="col-md-3 col-lg-6">
					<a class="footer-title" href="{{ home }}">{{ home_title }}</a>
					<p>Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh.</p>
				</div>
				<div class="col-md-3 col-lg-2">
					<h4 class="footer-title">Category 1</h4>
					<ul class="nav-footer">
						<li class="nav-item">
							<a class="nav-link" href="#">Home</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">About</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">Our Work</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">Contact</a>
						</li>
					</ul>
				</div>
				<div class="col-md-3 col-lg-2">
					<h4 class="footer-title">Category 2</h4>
					<ul class="nav-footer">
						<li class="nav-item">
							<a class="nav-link" href="#">Home</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">About</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">Our Work</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">Contact</a>
						</li>
					</ul>
				</div>
				<div class="col-md-3 col-lg-2">
					<h4 class="footer-title">Category 3</h4>
					<ul class="nav-footer">
						<li class="nav-item">
							<a class="nav-link" href="#">Home</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">About</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">Our Work</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#">Contact</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="container-fluid">
			<div class="divider"></div>
		</div>
		<div class="container">
			<div class="row">
				<div class="col-md-6 text-md-left">
					<p class="copyright">&copy; <a title="{{ home_title }}" href="{{ home }}">{{ home_title }}</a>, {{ lang.all_right_reserved }} Powered by <a title="Next Generation CMS" target="_blank" href="http://ngcms.ru/">NG CMS</a> 2007 — {{ now|date("Y") }}.</p>
				</div>
				<div class="col-md-6 text-md-right">
					{{ lang.sql_queries }}: <b>{{ queries }}</b>&nbsp;&nbsp;•&nbsp;&nbsp;{{ lang.page_generation }}: <b>{{ exectime }}</b> {{ lang.sec }}&nbsp;&nbsp;•&nbsp;&nbsp;<b>{{ memPeakUsage }}</b> Mb&nbsp;
				</div>
			</div>
		</div>
	</footer>

	<div id="loading-layer" class="col-md-3"><i class="fa fa-spinner fa-pulse"></i> {{ lang.loading }}</div>
</body>
</html>
[debug]{debug_queries}{debug_profiler}[/debug]
[/TWIG]