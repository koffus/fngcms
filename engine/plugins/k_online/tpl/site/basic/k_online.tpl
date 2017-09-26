<div class="widget widget-online_users">
    <div class="widget-header">
        <h4 class="widget-title">Кто онлайн</h4>
    </div>
    <div class="widget-body">
        <b>Всего на сайте: {{all}} </b><br />
        - Анонимов: {{num_guest}}<br />
        - Авторизированных: {{num_auth}}<br />
        <i>-- Команда сайта:</i> {{num_team}}<br />
        <i>-- Пользователи:</i> {{num_users}}<br />
        - Поисковых роботов: {{num_bot}}
        {% if (entries_team.true) %}
            <br /><br />
            {{entries_team.print}}
        {% endif %}
        {% if (entries_user.true) %}
            <br /><br />
            {{entries_user.print}}
        {% endif %}
        {% if (today.true) %}
            <br /><br />
            {{today.print}}
        {% endif %}
        {% if (entries_bot.true) %}
            <br /><br />
            {{entries_bot.print}}
        {% endif %}
        <!--ul class="list-unstyled">
            {% for item in items %}
                <li><a href="{{ item.link }}" title="{{ item.title }}">{{ item.title }}{% if (item.counter) %} ({{ item.cnt }}{{ item.ctext }}){% endif %}</a></li>
            {% endfor %}
        </ul-->
    </div>
</div>
