<div class="widget widget-archive">
    <h3 class="widget-title">{{ lang['archive:plugin_title'] }}</h3>
    <div class="widget-content">
        <ul class="list-unstyled">
            {% for entry in entries %}
                <li><a href="{{entry.link}}" title="{{ entry.title }}">{{ entry.title }}{% if (entry.counter) %} ({{ entry.cnt }}{{ entry.ctext }}){% endif %}</a></li>
            {% endfor %}
        </ul>
    </div>
</div>