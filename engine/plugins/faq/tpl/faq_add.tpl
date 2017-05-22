<!-- Page Header -->
<h1>Вопрос / Ответ</h1>
<h2>Добавление вопроса</h2>
<!-- Page Content -->
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
