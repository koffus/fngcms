<div class="widget widget-bookmarks">
    <h3 class="widget-title">{{ lang['bookmarks:bookmarks'] }}</h3>
    {% if (count) %}
        <ul class="list-unstyled">
            {% for entry in entries %}
                <li><a href="{{entry.link}}">{{entry.title}}</a></li>
            {% endfor %}
        </ul>
        <br><a href="{{bookmarks_page}}">{{ lang['bookmarks:all_bookmarks'] }}</a>
    {% else %}
        <p>{{ lang['bookmarks:noentries'] }}</p>
    {% endif %}
</div>