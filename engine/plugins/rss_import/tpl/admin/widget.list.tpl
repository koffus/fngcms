<table class="table table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th></th>
            <th>{{ lang['rss_import:widget_name'] }}</th>
            <th>{{ lang['rss_import:widget_title'] }}</th>
            <th>{{ lang['rss_import:widget_url'] }}</th>
            <th>{{ lang['rss_import:widget_skin'] }}</th>
            <th class="text-right">{{ lang['action'] }}</th>
        </tr>
    </thead>
    <tbody>
    {% for item in items %}
        <tr>
            <td>{{ item.id }}</td>
            <td>{% if item.active %}<i class="fa fa-check text-success"></i>{% else %}<i class="fa fa-times text-danger"></i>{% endif %}</td>
            <td><code>&#123;&#123; plugin_rss_import_{{ item.name }} }}</code></td>
            <td>{{ item.title }}</td>
            <td>{{ item.url }}</td>
            <td>{{ item.skin }}</td>
            <td class="text-right">
                <div class="btn-group">
                    <a href="admin.php?mod=extra-config&plugin=rss_import&action=widget_edit&id={{ item.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                    <a href="#" onclick="confirmIt('admin.php?mod=extra-config&plugin=rss_import&action=widget_dell&id={{ item.id }}','{{ lang['sure_del'] }}');return false;" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
                </div>
            </td>
        </tr>
    {% else %}
        <tr><td colspan="7">{{ lang['not_found'] }}</td></tr>
    {% endfor %}
    </tbody>
</table>