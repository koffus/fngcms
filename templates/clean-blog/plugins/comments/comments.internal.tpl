<section class="">
	<ul class="comments-list list-unstyled">
		{entries}
		<li id="new_comments"></li>
	</ul>

	<!-- Pager -->
	<nav><ul class="pagination justify-content-center">{more_comments}</ul></nav>

	{form}

	[regonly]
	<div class="alert alert-info">
		Уважаемый посетитель, Вы зашли на сайт как незарегистрированный пользователь.<br />
		Мы рекомендуем Вам <a href="{home}/register/">зарегистрироваться</a> либо <a href="{home}/login/">войти</a> на сайт под своим именем.
	</div>
	[/regonly]

	[commforbidden]
	<div class="alert alert-info">
		Комментирование данной новости запрещено.
	</div>
	[/commforbidden]
</section>