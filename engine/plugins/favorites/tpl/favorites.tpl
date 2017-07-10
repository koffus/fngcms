<div class="widget widget-favorites">
    <div class="widget-header">
        <h4 class="widget-title">{{ lang['favorites:plugin_title'] }}</h4>
    </div>
    <div class="widget-body">
        <ul class="list-unstyled">
            {% for entry in entries %}
                <li><a href="{{ entry.link }}" title="{{ entry.title }}">{{ entry.title }}{% if (entry.views) %} ({{ entry.views }}){% endif %}</a></li>
            {% endfor %}
        </ul>
    </div>
</div>