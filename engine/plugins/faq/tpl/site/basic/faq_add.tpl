<h2 class="section-title">Добавление вопроса</h2>

{%  if (info.status == 'error') %}
<div class="alert alert-danger">Ошибка добавления: не заполнены обязательные поля</div>
{%  elseif (info.status == 'success') %}
<div class="alert alert-success">Спасибо. Ваш вопрос добавлен!</div>
{%  endif %}
<form id="postForm" name="form" method="POST" action="">

	<div class="form-group row">
		<label class="col-sm-2 col-form-label">Вопрос</label>
		<div class="col-sm-10">
			<input type="text" name="question" value="" class="form-control" required />
		</div>
	</div><div class="form-group row">
		<div class="col-sm-10 offset-sm-2">
			<input type="submit" value="Отправить" class="btn btn-success" />
			<a href="{{ home }}/plugin/faq/" class="btn btn-secondary">Вернуться назад</a>
		</div>
	</div>
</form>
