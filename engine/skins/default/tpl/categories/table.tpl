<!-- Navigation bar -->
<ul class="breadcrumb">
    <li><a href="admin.php">{{ lang['home'] }}</a></li>
    <li class="active">{{ lang['categories_title'] }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
    <!-- List of categories: BEGIN -->
    <div class="panel panel-default panel-table">
        <div class="panel-heading text-right">
            {% if (flags.canModify) %}
                <a href="admin.php?mod=categories&action=add" title="{{ lang['addnew'] }}" class="btn btn-success"><i class="fa fa-plus"></i> </a>
            {% endif %}
        </div>
        <form name="static" action="admin.php?mod=static" method="post">
            <input type="hidden" name="token" value="{{ token }}">
            
            <div class="panel-body table-responsive">
                <table class="table table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ lang['title'] }}</th>
                            <th>{{ lang['alt_name'] }}</th>
                            <th>{{ lang['category.header.menushow'] }}</th>
                            <th>{{ lang['category.header.template'] }}</th>
                            <th>{{ lang['news'] }}</th>
                            <th class="text-right">{{ lang['action'] }}</th>
                        </tr>
                    </thead>
                    
                    <tbody id="admCatList">
                        {% for entry in entries %}
                        <tr>
                            <td>{{ entry.id }}</td>
                            <td>{{ entry.level }} {{ entry.name }} {% if (entry.info|length>0) %}<i class="fa fa-info" title="Категория содержит описание"></i>{% endif %}</td>
                            <td>{{ entry.alt }}</td>
                            <td>{% if (entry.flags.showMain) %}<i class="fa fa-check" title="{{ lang['yesa'] }}"></i>{% else %}<i class="fa fa-times" title="{{ lang['noa'] }}"></i>{% endif %}</td>
                            <td>{% if (entry.template == '') %}-_-{% else %}{{ entry.template }}{% endif %}</td>
                            <td><a href="admin.php?mod=news&amp;category={{ entry.id }}">{% if (entry.news == 0) %}-_-{% else %}{{ entry.news }}{% endif %}</a></td>
                            <td class="text-right">
                                <div class="btn-group">
                                    {% if (flags.canView) %}
                                        <a href="admin.php?mod=categories&amp;action=edit&amp;catid={{ entry.id }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
                                    {% endif %}
                                    <a class="btn btn-default" href="{{ entry.linkView }}" title="{{ lang['site.view'] }}" target="_blank"><i class="fa fa-external-link"></i></a>
                                    {% if (flags.canModify) %}
                                        <button type="button" class="btn btn-default" onclick="categoryModifyRequest('up', {{ entry.id }}); return false;"><i class="fa fa-arrow-up"></i></button>
                                        <button type="button" class="btn btn-default" onclick="categoryModifyRequest('down', {{ entry.id }}); return false;"><i class="fa fa-arrow-down"></i></button>
                                        <button type="button" class="btn btn-danger" onclick="categoryModifyRequest('del', {{ entry.id }}); return false;"><i class="fa fa-trash-o"></i></button>
                                    {% endif %}
                                </div>
                            </td>
                        </tr>
                        {% else %}
                        <tr><td colspan="7"><p>- {{ lang['not_found'] }} -</p></td></tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <!-- List of categories: END -->
</div>

<script>
// Process RPC requests for categories

function categoryModifyRequest(cmd, cid) {
    var url = '{{ admin_url }}/rpc.php';
    var method = '';
    var params = [];
    switch (cmd) {
        case 'up':
        case 'down':
        case 'del':
            method = 'admin.categories.modify';
            params = {'mode' : cmd, 'id' : cid, 'token' : '{{ token }}' };
            break;
    }
    if (method == '' || params == '') {
        $.notify({message: 'No RPC command'},{type: 'danger'});
        return false;
    }
    $.reqJSON(url, method, params, function(json) {
        if (json.content !== 'undefined') {
            if (json.infoText) {
                $.notify({message: json.infoText},{type: json.infoCode ? 'success' : 'danger'});
            }
            document.getElementById('admCatList').innerHTML = json.content;
        } else {
            $.notify({message: 'Template error: no content received from server for update, server response: '+json.response},{type: 'danger'});
        }
    });

    return false;
}
</script>
