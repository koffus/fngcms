<div class="widget widget-favorites">
    <h3 class="widget-title">{{ lang['favorites:plugin_title'] }}</h3>
    <div class="widget-content">
        <ul class="list-unstyled">
            {% for entry in entries %}
                <li><a href="{{ entry.link }}" title="{{ entry.title }}">{{ entry.title }}{% if (entry.views) %} ({{ entry.views }}){% endif %}</a></li>
            {% endfor %}
        </ul>
    </div>
</div>