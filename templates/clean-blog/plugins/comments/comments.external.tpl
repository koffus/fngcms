<!-- Page Header -->
<header class="intro-header" style="background-image: url('{tpl_url}/img/home-bg.jpg')">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<div class="post-heading">
					<a href="{link}"><h1>{title}</h1></a>
					<hr class="small">
					[comheader]<div class="subheading">Все комментарии посетителей</div>[/comheader]
				</div>
			</div>
		</div>
	</div>
</header>

<!-- Post Content -->
<section class="comments-list">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<ul class="list-unstyled">
					<div id="new_comments_rev"></div>
					{entries}
					<div id="new_comments"></div>
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
			</div>
		</div>
	</div>
</section>
