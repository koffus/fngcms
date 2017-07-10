<div class="widget widget-archive">
    <div class="widget-header">
        <h4 class="widget-title">{{ lang['archive:plugin_title'] }}</h4>
    </div>
    <div class="widget-body">
        <ul class="list-unstyled">
            {% for entry in entries %}
                <li><a href="{{entry.link}}" title="{{ entry.title }}">{{ entry.title }}{% if (entry.counter) %} ({{ entry.cnt }}{{ entry.ctext }}){% endif %}</a></li>
            {% endfor %}
        </ul>
    </div>
</div>