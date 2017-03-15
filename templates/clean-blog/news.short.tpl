[TWIG]
<article class="post-preview">
	<a href="{{ news.url.full }}"><h2 class="post-title">{{ news.title }}</h2></a>
	<p class="post-meta">Posted by {% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %} on {{ news.date }} {{ news.categories.masterText }}</p>
	<p class="post-img">
	{% if (news.embed.imgCount > 0) %}
		<img src="{{ news.embed.images[0] }}" class="img-fluid" />
	{% else %}
		<img src="{{ tpl_url }}/img/img-none.png" class="img-fluid" />
	{% endif %}
	</p>
	<p class="post-body">{{ news.short|truncateHTML(150,'...')|striptags }}</p>
</article>
<hr>
[/TWIG]