<table class="table table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ lang['rss_import:widget_name'] }}</th>
            <th>{{ lang['rss_import:widget_title'] }}</th>
            <th>{{ lang['rss_import:widget_url'] }}</th>
            <th>{{ lang['rss_import:widget_localSkin'] }}</th>
            <th class="text-right">{{ lang['action'] }}</th>
        </tr>
    </thead>
    <tbody>
    {% for item in items %}
        <tr>
            <td>{{ item.id }}</td>
            <td><code>&#123;&#123; plugin_rss_import_{{ item.name }} }}</code></td>
            <td>{{ item.title }}</td>
            <td>{{ item.url }}</td>
            <td>{{ item.localSkin }}</td>
            <td class="text-right">
                <div class="btn-group">
                    <a href="admin.php?mod=extra-config&plugin=rss_import&action=widget_edit&id={{ item.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                    <a href="#" onclick="confirmIt('admin.php?mod=extra-config&plugin=rss_import&action=widget_dell&id={{ item.id }}','{{ lang['sure_del'] }}');return false;" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
                </div>
            </td>
        </tr>
    {% else %}
        <tr><td colspan="9">{{ lang['not_found'] }}</td></tr>
    {% endfor %}
    </tbody>
</table>