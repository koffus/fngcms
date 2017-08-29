<ul class="navbar-nav">
    <li class="nav-item dropdown">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{{ lang['profile'] }} </a>
        <ul class="dropdown-menu dropdown-menu-right">
            {% if (global.flags.isLogged) %}
                {% if (global.user['status'] <= 3) %}
                    <li class="nav-item"><a href="{{ admin_url }}" class="dropdown-item">{{ lang['admin_panel'] }}</a></li>
                    <li class="nav-item"><a href="{{ addnews_link }}" class="dropdown-item">{{ lang['add_news'] }}</a></li>
                {% endif %}
                {% if pluginIsActive('nsm') %}
                    <li class="nav-item"><a href="{{ home }}/plugin/nsm/" class="dropdown-item">{{ lang['add_news'] }}</a></li>
                {% endif %}
                {% if pluginIsActive('uprofile') %}
                    <li class="nav-item"><a href="{{ profile_link }}" class="dropdown-item">{{ lang['edit_profile'] }}</a></li>
                {% endif %}
                {% if pluginIsActive('pm') %}
                    <li class="nav-item"><a href="{{ p.pm.link }}" class="dropdown-item">{{ lang['private_messages'] }} ({{ p.pm.pm_unread }})</a></li>
                {% endif %}
                <div class="dropdown-divider"></div>
                <li class="nav-item"><a href="{{ logout_link }}" class="dropdown-item">{{ lang['log_out'] }}</a></li>
            {% else %}
                <li class="nav-item"><a href="{{ login_link }}" class="dropdown-item">{{ lang['login'] }}</a></li>
                <li class="nav-item"><a href="{{ reg_link }}" class="dropdown-item">{{ lang['registration'] }}</a></li>
                <div class="dropdown-divider"></div>
                <li class="nav-item"><a href="{{ lost_link }}" class="dropdown-item">{{ lang['lostpassword'] }}</a></li>
            {% endif %}
        </ul>
    </li>
</ul>