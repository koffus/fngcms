<div class="widget widget-users">
    <div class="widget-header">
        <h4 class="widget-title">{{ lang['lastcomments:widget-title'] }}</h4>
    </div>
    <div class="widget-body">
        {% if entries %}
                {% for entry in entries %}
                <dl class="row mb-3">
                    <dt class="col-sm-9">
                        {% if (entry.author_id) and (pluginIsActive('uprofile')) %}<a href="{{ entry.author_link }}">{% endif %}<span class="text-uppercase">{{ entry.author }}</span>{% if (entry.author_id) and (pluginIsActive('uprofile')) %}</a>{% endif %}
                        <br> <small>{{ entry.dateStamp|cdate }}</small>
                    </dt>
                    <dd class="col-sm-3">
                        <img src="{{ entry.avatar_url }}" alt="{{ entry.name }}" class="img-thumbnail" />
                    </dd>
                    <dt class="col-sm-12">
                        <a href="{{ entry.link }}#comment_{{ entry.comnum }}" title="{{ entry.title }}">{{ entry.title }}</a>
                    </dt>
                    <dd class="col-sm-12 text-mutted">
                        {{ entry.text }}
                        {% if (entry.answer != '') %}<br><b>{{ entry.name }}</b>: {{ entry.answer }}{% endif %}
                    </dd>
                </dl>
                {% endfor %}
        {% else %}
            Комментариев пока что нету.
        {% endif %}
    </div>
</div>
