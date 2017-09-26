<div class="widget widget-archive">
    <div class="widget-header">
        <h4 class="widget-title">{{ widget_title }}</h4>
    </div>
    <div class="widget-body">
        <ul class="list-unstyled">
            {% for item in items %}
                <li><a href="{{ item.link }}" title="{{ item.title }}">{{ item.title }}{% if (item.counter) %} ({{ item.cnt }}{{ item.ctext }}){% endif %}</a></li>
            {% endfor %}
        </ul>
    </div>
</div>