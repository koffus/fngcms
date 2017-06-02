[TWIG]
<li class="media">
	<div class="media-left">
		<a href="{{ news.url.full }}">
			{% if (news.embed.imgCount > 0) %}
				<img src="{{ news.embed.images[0] }}" alt="{{ news.title }}" class="media-object" />
			{% else %}
				<img src="{{ tpl_url }}/img/img-none.png" alt="{{ news.title }}" class="media-object" />
			{% endif %}
		</a>
	</div>
	<div class="media-body">
		<h5 class="media-heading"><a href="{{ news.url.full }}">{{ news.title }}</a></h5>
		<small>Опубликовал <span>{% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %}</span> <span>{{ news.dateStamp|cdate }}</span> в категории <span>{{ news.categories.masterText }}</span></small>
		<p>{{ news.short|truncateHTML(150,'...')|striptags }}</p>
	</div>
</li>
[/TWIG]