[TWIG]
<li class="media">
	<div class="media-left">
		<a href="{{ news.url.full }}">
			{% if (news.embed.imgCount > 0) %}
				<img src="{{ news.embed.images[0] }}" class="media-object" />
			{% else %}
				<img src="http://placehold.it/900x300" class="media-object" />
			{% endif %}
		</a>
	</div>
	<div class="media-body">
		<h4 class="media-heading"><a href="{{ news.url.full }}">{{ news.title }}</a></h4>
		<p><small>
		Опубликовал <span>{% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %}</span> <span>{{ news.date }}</span> в категории <span>{{ news.categories.masterText }}</span> 
			</small></p>
		<p>{{ news.short|truncateHTML(150,'...')|striptags }}</p>
	</div>
</li>
[/TWIG]