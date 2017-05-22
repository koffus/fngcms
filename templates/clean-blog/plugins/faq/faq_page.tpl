<!-- Page Header -->
<header class="intro-header" style="background-image: url('{tpl_url}/img/home-bg.jpg')">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<div class="post-heading">
					<h1>Вопрос / Ответ</h1>
					<hr class="small">
					<span class="subheading">Мой первый блог</span>
				</div>
			</div>
		</div>
	</div>
</header>

<!-- Page Content -->
<div class="container">
	<div class="row">
		<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
			{% if (entries) %}
				<div id="faq-list" role="tablist" aria-multiselectable="true">
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
				</div>
			<hr>
			{% endif %}
			<a href="{{ home }}/plugin/faq/add/" class="btn btn-secondary">Добавить вопрос</a>
		</div>
	</div>
</div>