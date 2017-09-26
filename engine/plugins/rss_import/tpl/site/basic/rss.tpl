<div class="widget widget-rss_import">
    <div class="widget-header">
        <h4 class="widget-title">{{ widget_title }}</h4>
    </div>
    <div class="widget-body">
        <ul class="list-unstyled">
            {% for item in items %}
                <li>
                    <a href="{{ item.link }}" title="{{ item.title }}" target="_blank">{{ item.title }}</a>
                    <br />
                    {% for image in item.embed.images %}
                        <img src="{{ image }}" alt="{{ item.title }}" />
                    {% endfor %}
                    {% if item.description %}
                        {{ item.description }}
                    {% endif %} <small>{{ item.dateStamp|cdate }}</small>
                </li>
            {% endfor %}
        </ul>
    </div>
</div>