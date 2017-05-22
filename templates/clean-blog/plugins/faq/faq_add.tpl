<!-- Page Header -->
<header class="intro-header" style="background-image: url('{tpl_url}/img/home-bg.jpg')">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<div class="post-heading">
					<h1>Вопрос / Ответ</h1>
					<hr class="small">
					<span class="subheading">Добавление вопроса</span>
				</div>
			</div>
		</div>
	</div>
</header>

<!-- Page Content -->
<div class="container">
	<div class="row">
		<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
			{%  if (info.status == 'error') %}
			<div class="alert alert-danger">Ошибка добавления: не заполнены обязательные поля</div>
			{%  elseif (info.status == 'success') %}
			<div class="alert alert-success">Спасибо. Ваш вопрос добавлен!</div>
			{%  endif %}
			<form id="postForm" name="form" method="POST" action="">
				
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">Вопрос *:</label>
					<div class="col-sm-8">
						<div class="input-group">
							<input type="text" name="question" value="" class="form-control" />
							<span class="input-group-btn">
								<input type="submit" value="Отправить" class="btn btn-success" />
							</span>
						</div>
					</div>
				</div>
			</form>
			<hr>
			<a href="{{ home }}/plugin/faq/" class="btn btn-secondary">Вернуться назад</a>
		</div>
	</div>
</div>