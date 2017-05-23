<ul class="navbar-nav">
	{% if (global.flags.isLogged) %}
		<li class="nav-item dropdown">
			<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{{ lang['profile'] }} </a>
			<ul class="dropdown-menu dropdown-menu-right">
				[if-have-perm]
					<li class="nav-item"><a href="{{ admin_url }}" class="dropdown-item">{{ lang['admin_panel'] }}</a></li>
					<li class="nav-item"><a href="{{ addnews_link }}" class="dropdown-item">{{ lang['add_news'] }}</a></li>
				[/if-have-perm]
				{% if pluginIsActive('nsm') %}
					<li class="nav-item"><a href="{{ home }}/plugin/nsm/" class="dropdown-item">Добавить новость</a></li>
				{% endif %}
				{% if pluginIsActive('uprofile') %}
					<li class="nav-item"><a href="{{ profile_link }}" class="dropdown-item">{{ lang['edit_profile'] }}</a></li>
				{% endif %}
				{% if pluginIsActive('pm') %}
					<li class="nav-item"><a href="{{ p.pm.link }}" class="dropdown-item">{{ lang['private_messages'] }} ({{ p.pm.pm_unread }})</a></li>
				{% endif %}
				<li class="nav-item"><a href="{{ logout_link }}" class="dropdown-item">{{ lang.log_out }}</a></li>
			</ul>
		</li>
	{% else %}
		<li class="nav-item"><a href="{{ home }}/register/" class="nav-link">{{ lang['registration'] }}</a></li>
		<li class="nav-item"><a href="{{ home }}/login/" class="btn btn-outline-primary">{{ lang['login'] }}</a></li>
	{% endif %}
</ul>