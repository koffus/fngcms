{% if entries %}
	{% for entry in entries %}
	<div class="{{ entry.alternating }}">
		<div style="float:left; width:32px;padding: 5px;">
			<img style="width:100%;" src="{{ entry.avatar_url }}" />
		</div>
		<div>
			&raquo; {{ entry.text }} <small>({{ entry.dateStamp|cdate }})</small><br/>
			{% if (entry.answer != '') %}<div>Ответ от <b>{{ entry.name }}</b><br/>{{ entry.answer }}</div><br />{% endif %}
			<small>// <a href="{{ entry.link }}#comment_{{ entry.comnum }}" title="{{ entry.title }}">{{ entry.title }}</a></small><br/>
			Автор {% if (entry.author_id) and (pluginIsActive('uprofile')) %}<a target="_blank" href="{{ entry.author_link }}">{% endif %}{{ entry.author }}{% if (entry.author_id) and (pluginIsActive('uprofile')) %}</a>{% endif %}
		</div>
	</div>
	{% endfor %}
{% else %}
	Комментариев пока что нету.
{% endif %}
