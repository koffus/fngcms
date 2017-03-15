[TWIG]
<!-- Page Header -->
<header class="intro-header" style="background-image: url('{% if (news.embed.imgCount > 0) %}{{ news.embed.images[0] }}{% else %}{{ tpl_url }}/img/img-none.png{% endif %}')">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<div class="post-heading">
					<h1>{{ news.title }}</h1>
					<hr class="small">
					<div class="subheading">{{ category }}</div>
				</div>
			</div>
		</div>
	</div>
</header>

<!-- Post Content -->
<article>
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<p>{{ news.short|striptags }}</p>
				<p class="post-meta clearfix">
					<span class="pull-left">
						Posted by {% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %} on {{ news.date }}
					</span>
					<span class="pull-right">
						<i class="fa fa-eye" title="{{ lang.views }}"></i> {{ news.views }}{% if pluginIsActive('comments') %} | <i class="fa fa-eye" title="{{ lang.com }}"></i> {comments-num}{% endif %}
					</span>
				</p>
				<hr class="alert-info">
				<p>{{ news.full }}</p>
				{% if (news.flags.hasPagination) %}
				<!-- Pager -->
				<nav><ul class="pagination justify-content-center">{{ news.pagination }}</ul></nav>
				{% endif %}
			</div>
		</div>
	</div>
</article>

{% if pluginIsActive('comments') %}
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				{{ plugin_comments }}
			</div>
		</div>
	</div>
{% endif %}

[/TWIG]