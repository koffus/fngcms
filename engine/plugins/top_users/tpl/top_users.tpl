<div class="widget widget-pages">
    <h3 class="widget-title">{{ lang['top_users:widget-title'] }}</h3>
    <div class="widget-content">
        <dl class="row">
            {% for entry in entries %}
                <dt class="col-sm-4">
                    {% if (entry.use_avatars) %}<img src="{{ entry.avatar_url }}" alt="{{ entry.name }}" class="img-thumbnail" />{% endif %}
                </dt>
                <dd class="col-sm-8">
                    <a href="{{entry.link}}" title="{{ entry.name }}" class="text-uppercase">{{ entry.name }}</a>
                    <p>
                    {% if pluginIsActive('comments') %}
                        {{ lang['top_users:widget-com'] }} <span class="pull-right">{{ entry.com }}</span>
                        <br />
                    {% endif %}
                    {{ lang['top_users:widget-news'] }} <span class="pull-right">{{ entry.news}}</span>
                    </p>
                </dd>
            {% endfor %}
        </dl>
    </div>
</div>


