<ul class="nav nav-tabs nav-justified">
	<li class="{{ class['news'] }}"><a href="admin.php?mod=extra-config&plugin=xfields&section=news">{{ lang['xfields:section.news'] }}</a></li>
	<li class="{{ class['grp.news'] }}"><a href="admin.php?mod=extra-config&plugin=xfields&section=grp.news">{{ lang['xfields:section.grp.news'] }}</a></li>
	<li class="{{ class['tdata'] }}"><a href="admin.php?mod=extra-config&plugin=xfields&section=tdata">{{ lang['xfields:section.tdata'] }}</a></li>
	{% if (pluginIsActive('uprofile')) %}
	<li class="{{ class['users'] }}"><a href="admin.php?mod=extra-config&plugin=xfields&section=users">{{ lang['xfields:section.users'] }}</a></li>
	{% endif %}
</ul>