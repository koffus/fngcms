<h2>Вопрос / Ответ</h2>
{% if (entries) %}
	<dl>
		{% for entry in entries %}
			<dt>{{ entry.question }}</dt>
			<dd>{{ entry.answer }}</dd>
		{% endfor %}
	</dl>
<hr>
{% endif %}
<a href="{{ home }}/plugin/faq/" class="btn btn-secondary">Все вопросы</a>