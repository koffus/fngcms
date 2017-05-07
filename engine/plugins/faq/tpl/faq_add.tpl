{%  if (info.status == 'error') %}
<div>Ошибка добавления<br/>Не заполнены обязательные поля</div>
{%  elseif (info.status == 'success') %}
<div>Спасибо. Ваш вопрос добавлен!</div>
{%  endif %}
<form id="postForm" name="form" method="POST" action="">
	<table class="table table-striped table-bordered">
		<tr>
			<td>Вопрос *:</td>
			<td><input type="text" name="question" class="input" value=""/></td>
		</tr>
	</table>
	<div class="clearfix"></div>
	<div class="label pull-right">
		<input class="button" type="submit" value="Отправить"/>
	</div>
</form>