<!-- Navigation bar -->
<ul class="breadcrumb">
    <li><a href="admin.php">{{ lang['home'] }}</a></li>
    <li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
    <li class="active">{{ lang['manage'] }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
    <div class="well">
        {{ lang['manage.description'] }}</b>
    </div>
    <form method="post" action="admin.php?mod=extras&manageConfig=1">
        <input type="hidden" name="token" value="{{ token }}"/>
        <input type="hidden" name="action" value="commit"/>

        <!-- List of plugins: BEGIN -->
        <div id="configAreaX"></div>
        <!-- List of plugins: End -->
        <div class="form-group">
            <textarea name="config" id="configArea" rows="14" class="form-control"></textarea>
        </div>
        <div class="well text-center">
            <!-- <input type="submit" value="Commit changes"/> -->
            <input type="button" value="Load data" onclick="loadData(); return false;" class="btn btn-primary"/>
        </div>
    </form>
</div>

<script>
function loadData() {
    var url = '{{ admin_url }}/rpc.php';
    var method = 'admin.extras.getPluginConfig';
    var params = {'token': '{{ token }}',};
    $.reqJSON(url, method, params, function(json) {
        var line = json.content;
        var newline = line.replace(/\\u/g, "%u");
        $('#configArea').val(unescape(newline));
    });
}
</script>