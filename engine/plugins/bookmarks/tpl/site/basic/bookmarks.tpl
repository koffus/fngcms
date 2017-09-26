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
    {% else %}
        <p>{{ lang['bookmarks:noentries'] }}</p>
    {% endif %}
    </div>
    <div class="widget-footer">
        <p><a href="{{ bookmarks_page }}" class="text-muted pull-right">{{ lang['bookmarks:all_bookmarks'] }} &raquo;</a></p>
    </div>
</div>