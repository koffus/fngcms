[TWIG]
<article class="article-item-wrapper">
	<div class="article-item-img">
		<a href="{{ news.url.full }}">
			{% if (news.embed.imgCount > 0) %}
				<img src="{{ news.embed.images[0] }}" alt="{{ news.title }}" />
			{% else %}
				<img src="{{ tpl_url }}/img/img-none.png" alt="{{ news.title }}" />
			{% endif %}
		</a>
	</div>
	<div class="article-item-text">
		<h3 class="small-title"><a href="{{ news.url.full }}">{{ news.title }}</a></h3>
		<p>{{ news.short|truncateHTML(200,' ...')|striptags }}</p>
		<div class="article-one-footer">
			<span class="mr-auto"><i class="fa fa-calendar"></i> {{ news.date }}</span>
			<span><i class="fa fa-eye"></i> {{ news.views }}</span>
			{{ news.categories.masterText }}
		</div>
	</div>
</article>

<!--article class="card mb-5">
		{% if (news.embed.imgCount > 0) %}
			<img src="{{ news.embed.images[0] }}" class="card-img-top" />
		{% else %}
			<img src="{{ tpl_url }}/img/img-none.png" class="card-img-top" />
		{% endif %}
	<div class="card-block">
		<h3 class="card-title"><a href="{{ news.url.full }}">{{ news.title }}</a></h3>
		<p class="card-text"><span class="text-muted">{{ news.date }}</span>&nbsp;&nbsp;•&nbsp;&nbsp;<span>{% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %}</span>&nbsp;&nbsp;•&nbsp;&nbsp;<span class="text-muted">{{ news.categories.masterText }}</span></p>
		<p class="card-text">{{ news.short|truncateHTML(200,' ...')|striptags }}</p>
	</div>
</article-->
[/TWIG]