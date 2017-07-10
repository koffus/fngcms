<div class="widget widget-bookmarks">
    <div class="widget-header">
        <h4 class="widget-title">{{ lang['bookmarks:bookmarks'] }}</h4>
    </div>
    <div class="widget-body">
    {% if (entries) %}
        <ul class="list-unstyled">
            {% for entry in entries %}
                <li><a href="{{entry.link}}" title="{{entry.title}}">{{entry.title}}</a></li>
            {% endfor %}
        </ul>
        <p><a href="{{bookmarks_page}}">{{ lang['bookmarks:all_bookmarks'] }}</a></p>
    {% else %}
        <p>{{ lang['bookmarks:noentries'] }}</p>
    {% endif %}
    </div>
</div>