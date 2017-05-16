<!-- Page Header -->
<article class="full-post">
	<h2 class="page-header">{title}</h2>
	<p>Все комментарии посетителей к записи <a href="{link}">{title}</a></p>
</article>

<!-- Post Content -->
<section class="section comments-list">

	<ul class="list-unstyled">
		{entries}
		<li id="new_comments"></li>
	</ul>

	<nav>
		<ul class="pagination">{more_comments}</ul>
	</nav>

	{form}

	[regonly]
	<div class="alert alert-info">
		Уважаемый посетитель, Вы зашли на сайт как незарегистрированный пользователь.<br />
		Мы рекомендуем Вам <a href="{home}/register/">зарегистрироваться</a> либо <a href="{home}/login/">войти</a> на сайт под своим именем.
	</div>
	[/regonly]

	[commforbidden]
	<div class="alert alert-danger">
		Комментирование данной новости запрещено.
	</div>
	[/commforbidden]
</section>
