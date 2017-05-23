[TWIG]Прикрепленные файлы: {{ debugValue(_files) }}<br/>
Прикрепленные картинки: {{ debugValue(_images) }}
{% for file in _files %}
* <a href="{{ file.url }}">{{ file.origName }}</a><br/>
{% endfor %}
[/TWIG]
[TWIG]
<article class="full-post">
	<h1 class="title">{{ news.title }}</h1>
	<span class="meta">{{ news.date }} | {% if pluginIsActive('uprofile') %}<a href="{{ news.author.url }}">{% endif %}{{ news.author.name }}{% if pluginIsActive('uprofile') %}</a>{% endif %}</span>
	[xfield_tested]
     Данный адаптер был протестирован в нашей лаборатории.<br/>
     [xfield_vendor]Производитель: [xvalue_vendor]<br/>[/xfield_vendor]
     [xfield_reldate]Дата выхода на рынок: [xvalue_reldate]<br/>[/xfield_reldate]
    [xfield_multi]Установка нескольких адаптеров в систему: [xvalue_multi]<br/>[/xfield_multi]
     [xfield_index]Индекс производительности: [xvalue_index]<br/>[/xfield_index]
     [xfield_result]Мнение ред. коллегии:<div>[xvalue_result]</div>[/xfield_result]
    [/xfield_tested]
    <p>{{ news.short }}{{ news.full }}</p>
	{% if (news.flags.hasPagination) %}
		<div class="pagination">
			<ul>
				{{ news.pagination }}
			</ul>
		</div>
	{% endif %}
	<div class="post-full-footer">
		{% if pluginIsActive('tags') %}{% if (p.tags.flags.haveTags) %}<div class="post-full-tags">{{ lang.tags }}: {{ tags }}</div>{% endif %}{% endif %}
        <div class="post-full-tags">Категории: {{ news.categories.text }}</div>
		<div class="post-full-meta">{{ lang.views }}: {{ news.views }} {% if pluginIsActive('comments') %}| {{ lang.com }}: {comments-num}{% endif %}</div>
		{% if pluginIsActive('rating') %}<div class="post-rating">{{ lang.rating }}: <span class="post-rating-inner">{{ plugin_rating }}</span></div>{% endif %}
	</div>
</article>
{% if pluginIsActive('similar') %}{{ plugin_similar_tags }}{% endif %}
{% if pluginIsActive('comments') %}
	<div class="title">{{ lang.comments }} ({comments-num})</div>
	{{ plugin_comments }}
{% endif %}
[/TWIG]