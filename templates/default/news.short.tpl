[TWIG]
<article class="article-item-wrapper">
    <div class="article-item-img">
        <a href="{{ news.url.full }}">
            {% if (news.embed.imgCount > 0) %}
                <img src="{{ news.embed.images[0] }}" alt="{{ news.title }}" />
            {% else %}
                <img src="{{ tpl_url }}/img/img-none.png" alt="{{ news.title }}" />
            {% endif %}
        </a>
    </div>
    <div class="article-item-text clearfix">
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></button>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ news.url.print }}" class="dropdown-item"><i class="fa fa-print"></i> {{ lang.print }}</a>
                {% if (news.flags.canEdit) %}<a href="{{ news.url.edit }}" class="dropdown-item"><i class="fa fa-pencil"></i> {{ lang.editnews }}</a>{% endif %}
                {% if pluginIsActive('bookmarks') %}{{ plugin_bookmarks_news }}{% endif %}
                {% if (news.flags.canDelete) %}
                    <div class="dropdown-divider"></div>
                    <a href="#" onclick="confirmIt('{{ news.url.delete }}', '{{ lang['sure_del'] }}'); return false;" target="_blank" class="dropdown-item"><i class="fa fa-trash"></i> {{ lang.delnews }}</a>
                {% endif %}
            </div>
        </div>
        <h3 class="small-title"><a href="{{ news.url.full }}">{{ news.title }}</a></h3>
        <h4>{{ news.categories.masterText }}</h4>
        <p>{{ news.short|truncateHTML(200,' ...')|striptags }}</p>
        <div class="article-one-footer">
            <span class="mr-auto"><i class="fa fa-calendar"></i>&nbsp;{{ news.dateStamp | cdate  }}</span>
            {% if (news.flags.isUpdated) %}<span class="mr-auto"><i class="fa fa-refresh"></i>&nbsp;{{ news.updateStamp | cdate }}</span>{% endif %}
            
            <span><i class="fa fa-eye"></i> {{ news.views }}</span>
        </div>
    </div>
</article>
[/TWIG]