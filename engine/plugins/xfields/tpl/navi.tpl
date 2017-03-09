<ul class="nav nav-tabs nav-justified">
	<li class="{{ class['news'] }}"><a href="admin.php?mod=extra-config&plugin=xfields&section=news">Новости: поля</a></li>
	<li class="{{ class['grp.news'] }}"><a href="admin.php?mod=extra-config&plugin=xfields&section=grp.news">Новости: группы</a></li>
	<li class="{{ class['tdata'] }}"><a href="admin.php?mod=extra-config&plugin=xfields&section=tdata">Новости: таблицы</a></li>
	{% if (pluginIsActive('uprofile')) %}
	<li class="{{ class['users'] }}"><a href="admin.php?mod=extra-config&plugin=xfields&section=users">Пользователи: поля</a></li>
	{% endif %}
</ul>
