<h2 class="section-title">{{ lang['lastcomments:plugin-title'] }}</h2>

<section class="section comments-list">
{% if entries %}
    <ul class="list-unstyled">
    {% for entry in entries %}
        <li class="comment clearfix {{ alternating }}" itemscope="" itemtype="http://schema.org/Comment">
            <div class="comment-content post-content" itemprop="text">
                <figure class="gravatar">
                    <img src="{{ entry.avatar_url }}" alt="{{ entry.name }}" />
                </figure>
                <div class="comment-meta">
                    <div class="comment-author">
                        {% if (entry.author_id) and (pluginIsActive('uprofile')) %}<a href="{{ entry.author_link }}">{% endif %}<span itemprop="author" class="text-uppercase">{{ entry.author }}</span>{% if (entry.author_id) and (pluginIsActive('uprofile')) %}</a>{% endif %}
                    </div>
                    <span title="{{ entry.date }}" class="pl-2">&nbsp;{{ entry.dateStamp | cdate  }}</span>
                    <p><a href="{{ entry.link }}#comment_{{ entry.comnum }}" title="{{ entry.title }}">{{ entry.title }}</a></p>
                    <p>{{ entry.text }}</p>
                    {% if (entry.answer != '') %}<p class="well well-sm text-muted">{{ lang['lastcomments:answer'] }} <b>{{ entry.name }}</b>:<br />{{ entry.answer }}</p>{% endif %}
                </div>
            </div>
        </li>
    {% endfor %}
    </ul>
{% else %}
    Комментариев пока что нету.
{% endif %}
</section>