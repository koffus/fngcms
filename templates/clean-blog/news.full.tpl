[TWIG]
<!-- Page Header -->
<header class="intro-header">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<div class="post-heading">
					<h1>{{ news.title }}</h1>
					<span class="meta">{{ news.date }} | {% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %}</span>
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
				{{ news.short }}{{ news.full }}
				
				{% if (news.flags.hasPagination) %}
				<!-- Pager -->
				<nav>
					<ul class="pagination justify-content-center">
						{{ news.pagination }}
					</ul>
				</nav>
				{% endif %}
				<div class="post-full-footer">
					{% if pluginIsActive('tags') %}{% if (p.tags.flags.haveTags) %}<div class="post-full-tags">{{ lang.tags }}: {{ tags }}</div>{% endif %}{% endif %}
					<div class="post-full-meta">{{ lang.views }}: {{ news.views }} {% if pluginIsActive('comments') %}| {{ lang.com }}: {comments-num}{% endif %}</div>
					{% if pluginIsActive('rating') %}<div class="post-rating">{{ lang.rating }}: <span class="post-rating-inner">{{ plugin_rating }}</span></div>{% endif %}
				</div>
			</div>
		</div>
	</div>
</article>

{% if pluginIsActive('similar') %}
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				{{ plugin_similar_tags }}
			</div>
		</div>
	</div>
{% endif %}

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