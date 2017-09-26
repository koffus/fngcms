<h2 class="section-title">Вопрос / Ответ</h2>
{% if (entries) %}
	{% for entry in entries %}
		<dl class="spoiler">
			<dt class="sp-head">{{ entry.question }}</dt>
			<dd class="sp-body" style="display: none;">{{ entry.answer }}</dd>
		</dl>
	{% endfor %}
<hr>
{% endif %}
<a href="{{ home }}/plugin/faq/add/" class="btn btn-secondary">Добавить вопрос</a>
