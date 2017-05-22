{% if (entries) %}
	<section class="questions">
		<h2>Вопрос / Ответ</h2>
		{% for entry in entries %}
			<div class="card">
				<div class="card-header" role="tab" id="heading{{ entry.id }}">
					<h5 class="mb-0">
						<a href="#faq-list-{{ entry.id }}" data-toggle="collapse" data-parent="#faq-list" aria-controls="faq-list-{{ entry.id }}">{{ entry.question }}</a>
					</h5>
				</div>

				<div id="faq-list-{{ entry.id }}" class="collapse" role="tabpanel" aria-labelledby="heading{{ entry.id }}">
					<div class="card-block">{{ entry.answer }}</div>
				</div>
			</div>
		{% endfor %}
		<a href="{{ home }}/plugin/faq/" class="btn btn-secondary">Все вопросы</a>
	</section>
{% endif %}