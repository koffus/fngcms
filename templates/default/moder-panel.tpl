<div class="btn-group pull-right">
    <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i></button>
    <div class="dropdown-menu dropdown-menu-right">
        <a href="{{ news.url.print }}" class="dropdown-item"><i class="fa fa-print"></i> {{ lang.print }}</a>
        {% if (news.flags.canEdit) %}<a href="{{ news.url.edit }}" class="dropdown-item"><i class="fa fa-pencil"></i> {{ lang.editnews }}</a>{% endif %}
        {% if pluginIsActive('bookmarks') %}{{ plugin_bookmarks_news }}{% endif %}
        {% if pluginIsActive('basket') %}[basket]<a href="#" onclick="rpcBasketRequest('{{ admin_url }}/rpc.php', 'plugin.basket.manage', {'action': 'add', 'ds':1,'id':{news-id},'count':1}); return false;" class="dropdown-item"><i class="fa fa-shopping-cart"></i> В корзину</a>[/basket]{% endif %}
        {% if (news.flags.canDelete) %}
            <div class="dropdown-divider"></div>
            <a href="#" onclick="confirmIt('{{ news.url.delete }}', '{{ lang['sure_del'] }}'); return false;" target="_blank" class="dropdown-item"><i class="fa fa-trash"></i> {{ lang.delnews }}</a>
        {% endif %}
    </div>
</div>