<!-- Navigation bar -->
<ul class="breadcrumb">
    <li><a href="admin.php">{{ lang['home'] }}</a></li>
    <li><a href="admin.php?mod=news">{{ lang.news['news_title'] }}</a></li>
    <li class="active">{{ lang.news['addnews_title'] }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
    <!-- Main content form -->
    <form name="form" id="postForm" action="admin.php?mod=news" method="post" enctype="multipart/form-data" target="_self" class="form-horizontal">
        <input type="hidden" name="token" value="{{ token }}" />
        <input type="hidden" name="mod" value="news" />
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="subaction" value="submit" />

        <div class="row">
            <div class="col col-sm-8">
                <!-- MAIN CONTENT -->
                <div class="panel panel-default">
                    <div class="panel-heading"><h4 class="panel-title"><i class="fa fa-th-list"></i> {{ lang['maincontent'] }}</h4></div>
                    <div id="maincontent" class="panel-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">
                                {{ lang.news['title'] }}
                                </label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" name="title" id="newsTitle" value="" tabindex="1" class="form-control"/>
                                    <span class="input-group-btn">
                                        <button type="button" onclick="searchDouble();" class="btn btn-default" title="Поиск дубликатов"><i class="fa fa-files-o"></i></button>
                                    </span>
                                </div>
                                <div id="searchDouble"></div>
                            </div>
                        </div>
                        {% if not flags['altname.disabled'] %}
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ lang.news['alt_name'] }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="alt_name" value="" tabindex="2" class="form-control"/>
                            </div>
                        </div>
                        {% endif %}
                        <div class="form-group">
                            <label class="col-sm-3 control-label">
                                {{ lang.news['category'] }}
                                {% if (flags.mondatory_cat) %} <span class="text-danger"><b>*</b></span>{% endif %}
                            </label>
                            <div class="col-sm-9">{{ mastercat }}</div>
                        </div>
                        <div id="fullwidth" class="form-group">
                            <div class="col-sm-12">
                                {% if not (isBBCode) %}
                                    {{ bbcodes }}
                                    <!-- SMILES -->
                                    <div id="modal-smiles" class="modal fade" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-sm" role="document">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    <h4>Вставить смайл</h4>
                                                </div>
                                                <div class="modal-body text-center smiles">
                                                    {{ smilies }}
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="cancel" class="btn btn-default" data-dismiss="modal">{{ lang['cancel'] }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {% endif %}
                                <textarea name="ng_news_content" {% if (isBBCode) %}class="{{ attributBB }} form-control"{% else %}id="ng_news_content" class="form-control ng_news_content"{% endif %} rows="10" tabindex="5"></textarea>
                            </div>
                        </div>
                        {% if (flags.meta) %}
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ lang.news['description'] }}</label>
                            <div class="col-sm-9">
                                <textarea name="description" class="form-control" rows="4" tabindex="6"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ lang.news['keywords'] }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="keywords" id="newsKeywords" value="" tabindex="7" class="form-control" maxlength="255" />
                            </div>
                        </div>
                        {% endif %}
                        <!-- PLUGIN IN MAIN BLOCK -->
                        {% if (extends.main) %}
                            {% for entry in extends.main %}
                                {{ entry.body }}
                            {% endfor %}
                        {% endif %}
                    </div>
                </div>

                <div class="panel-group" id="accordion">
                    <!-- PLUGIN IN ADDITIONAL BLOCK -->
                    {% if (extends.additional or pluginIsActive('xfields')) %}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title"><a href="#additional" data-toggle="collapse" data-parent="#accordion">{{ lang.news['bar.additional'] }}</a></h4>
                        </div>
                        <div id="additional" class="panel-collapse collapse">
                            <div class="panel-body">
                                {% for entry in extends.additional %}
                                    <legend>{{ entry.header_title }}</legend>
                                    {{ entry.body }}
                                {% endfor %}
                            </div>
                        </div>
                    </div>
                    {% endif %}

                    <!-- PLUGIN WITH OWNER BLOCK -->
                    {% if (extends.owner) %}
                        {% for entry in extends.owner %}
                        <div class="panel panel-default {% if(entry.table) %}panel-table{% endif %}">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a href="#panel-owner-{{ loop.index }}" data-toggle="collapse" data-parent="#accordion">{{ entry.header_title }}</a></h4>
                            </div>
                            <div id="panel-owner-{{ loop.index }}" class="panel-collapse collapse"><div class="panel-body">{{ entry.body }}</div></div>
                        </div>
                        {% endfor %}
                    {% endif %}

                    <!-- ATTACHES -->
                    <div class="panel panel-default panel-table">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a href="#attaches" data-toggle="collapse" data-parent="#accordion">{{ lang.news['bar.attaches'] }}</a>
                            </h4>
                        </div>
                        <div id="attaches" class="panel-collapse collapse" aria-expanded="false">
                            <div class="panel-body">
                                <table id="attachFilelist" class="table table-condensed">
                                    <thead>
                                        <tr>
                                            <th>{{ lang['attach.filename'] }} - {{ lang['attach.size'] }}</th>
                                            <th class="text-center" width="10">{{ lang['action'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td class="text-center" width="10">
                                                <button type="button" title="{{ lang['attach.more_rows'] }}" onclick="attachAddRow('attachFilelist');" class="btn btn-primary" title="{{ lang['attach.more_rows'] }}"><i class="fa fa-plus"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT Form -->
                <div class="well panel-fixed-bottom">
                    <div class="row">
                        <div class="col col-xs-6">
                            <select name="approve" class="form-control">
                                {% if flags['can_publish'] %}
                                    <option value="1">{{ lang.news['publish'] }}</option>
                                {% endif %}
                                <option value="0">{{ lang.news['send_moderation'] }}</option>
                                <option value="-1">{{ lang.news['save_draft'] }}</option>
                            </select>
                        </div>
                        <div class="col col-xs-6 text-right">
                            <button type="submit" title="Ctrl+S {{ lang.news['publish'] }}" class="btn btn-success">
                                <span class="visible-sm-block visible-xs-block"><i class="fa fa-floppy-o"></i></span>
                                <span class="hidden-sm hidden-xs">{{ lang['add'] }}</span>
                            </button>
                            <button type="button" title="{{ lang.news['preview'] }}" onclick="return preview();" class="btn btn-primary"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right edit column -->
            <div id="rightBar" class="col col-sm-4">
                {% if flags['multicat.show'] %}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ lang['editor.extcat'] }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="has-feedback" onclick="$('.cat-list').toggle();">
                            <input id="catSelector" class="form-control" type="button" value="{{ lang['no_cat'] }}" hidefocus="" autocomplete="off" readonly="" style="white-space: pre-wrap;height: auto; text-align: left;">
                            <span class="form-control-feedback"><span class="caret"></span></span>
                        </div>
                        <div class="cat-list" style="display: none;">{{ extcat }}</div>
                    </div>
                </div>
                {% endif %}
                <div class="panel panel-default">
                    <div class="panel-heading"><h4 class="panel-title">{{ lang['editor.configuration'] }}</h4></div>
                    <div class="panel-body">
                            <label>
                                <input type="checkbox" name="mainpage" value="1" id="mainpage" {% if (flags.mainpage) %}checked="checked" {% endif %}{% if flags['mainpage.disabled'] %}disabled {% endif %}/>&nbsp;
                                {{ lang.news['mainpage'] }}
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="pinned" value="1" id="pinned" {% if (flags.pinned) %}checked="checked" {% endif %}{% if flags['pinned.disabled'] %}disabled {% endif %} />&nbsp;
                                {{ lang.news['add_pinned'] }}
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="catpinned" value="1" id="catpinned" {% if (flags.catpinned) %}checked="checked" {% endif %}{% if flags['catpinned.disabled'] %}disabled {% endif %} />&nbsp;
                                {{ lang.news['add_catpinned'] }}
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="favorite" value="1" id="favorite" {% if (flags.favorite) %}checked="checked" {% endif %}{% if flags['favorite.disabled'] %}disabled {% endif %} />&nbsp;
                                {{ lang.news['add_favorite'] }}
                            </label>
                            <br />
                            <label>
                                <input name="flag_HTML" type="checkbox" id="flag_HTML" value="1" {% if (flags['html.disabled']) %}disabled {% endif %} {% if (flags['html']) %}checked="checked"{% endif %}/>&nbsp;
                                {{ lang.news['flag_html'] }}
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="flag_RAW" value="1" id="flag_RAW" {% if (flags['html.disabled']) %}disabled {% endif %} {% if (flags['raw']) %}checked="checked"{% endif %}/>&nbsp;
                                {{ lang.news['flag_raw'] }}
                            </label>
                    </div>
                </div>
                {% if not flags['customdate.disabled'] %}
                <div class="panel panel-default">
                    <div class="panel-heading">{{ lang.news['custom_date'] }}</div>
                    <div class="panel-body">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <input type="checkbox" name="customdate" id="customdate" value="customdate" />
                            </span>
                            <input type="text" id="cdate" name="cdate" value="{{ cdate }}" class="form-control"/>
                        </div>
                    </div>
                </div>
                {% endif %}
                {% if (pluginIsActive('comments')) %}
                <div class="panel panel-default">
                    <div class="panel-heading">{{ lang['comments:mode.header'] }}</div>
                    <div class="panel-body">
                        <select name="allow_com" class="form-control">
                            <option value="0"{{ plugin.comments['acom:0'] }}>{{ lang['comments:mode.disallow'] }}</option>
                            <option value="1"{{ plugin.comments['acom:1'] }}>{{ lang['comments:mode.allow'] }}</option>
                            <option value="2"{{ plugin.comments['acom:2'] }}>{{ lang['comments:mode.default'] }}</option>
                        </select>
                    </div>
                </div>
                {% endif %}
            </div>
        </div>
    </form>
</div>

<!-- Hidden SUGGEST div -->
<div id="suggestWindow" class="suggestWindow"><table id="suggestBlock" cellspacing="0" cellpadding="0" width="100%"></table><a href="#" align="right" id="suggestClose">{{ lang['close'] }}</a></div>

<form name="DATA_tmp_storage" action="" id="DATA_tmp_storage"><input type="hidden" name="area" value="" /></form>

{% if (extends.css) %}
    {% for entry in extends.css %}
        {{ entry.body }}
    {% endfor %}
{% endif %}
{% if (extends.js) %}
    {% for entry in extends.js %}
        {{ entry.body }}
    {% endfor %}
{% endif %}

<script src="{{ scriptLibrary }}/libsuggest.js"></script>

<link href="{{ scriptLibrary }}/datetimepicker-4.15.35/datetimepicker.css" rel="stylesheet">
<script src="{{ scriptLibrary }}/js/moment-2.17.1.js"></script>
<script src="{{ scriptLibrary }}/datetimepicker-4.15.35/datetimepicker.js"></script>

<script>
<!--

// Global variable: ID of current active input area
var currentInputAreaID = 'ng_news_content';
var form = document.getElementById('postForm');

$(function() {
    $('#cdate').datetimepicker({format:'DD.MM.YYYY HH:mm',locale: "{{ lang['langcode'] }}"});
});

function insertselcat(cat) {
    var ss = $('select[name=category] option:selected');
    if( ss.val() == 0 ) {
        $('input[name*=category_]').map(function() {$(this).prop('checked', false);});
    }
    var ctstring = $('input[name*=category_]:checked').map(function() {
        if( $.trim(ss.text()) != $.trim($(this).parent().text()) ) {
            return $(this).parent().text();
        } else {
            $(this).prop('checked', false);
        }
    }).get().join(", ") || "{{ lang['no_cat'] }}";
    $("#catSelector").val( ctstring.replace(/&amp;/g, '&') );
}

$('input[name*=category_], select[name=category]').on('click', function (e) {
    insertselcat();
});

function preview(){

    if (form.ng_news_content.value == '' || form.title.value == '') {
        $.notify({message: '{{ lang.news['msge_preview'] }}'},{type: 'danger'});
        return false;
    }

    form['mod'].value = "preview";
    form.target = "_blank";
    form.submit();

    form['mod'].value = "news";
    form.target = "_self";

    return true;
}

// Add first row
var flagRequireReload = 0;
attachAddRow('attachFilelist');

// HotKeys to this page
document.onkeydown = function(e) {
    e = e || event;

    if (e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
        form.submit();
        return false;
    }
}
-->
</script>

<script>
// Restore variables if needed
var jev = {{ JEV }};
var form = document.getElementById('postForm');
for (i in jev) {
 //try { alert(i+' ('+form[i].type+')'); } catch (err) {;}
 if (typeof(jev[i]) == 'object') {
    for (j in jev[i]) {
        //alert(i+'['+j+'] = '+ jev[i][j]);
        try { form[i+'['+j+']'].value = jev[i][j]; } catch (err) {;}
    }
 } else {
 try {
 if ((form[i].type == 'text')||(form[i].type == 'textarea')||(form[i].type == 'select-one')) {
 form[i].value = jev[i];
 } else if (form[i].type == 'checkbox') {
 form[i].checked = (jev[i]?true:false);
 }
 } catch(err) {;}
 }
}

</script>

<script>
var searchDouble = function() {
    if ($.trim($('#newsTitle').val()).length < 4)
        return $.notify({message: '{{ lang.news['msge_title'] }}'},{type: 'danger'});
    var url = '{{ admin_url }}/rpc.php';
    var method = 'admin.news.double';
    var params = {'token': '{{ token }}','title': $('#newsTitle').val(),'mode': 'add',};
    $.reqJSON(url, method, params, function(json) {
        $('#searchDouble').html('');
        if (json.info) {
            $.notify({message:json.info},{type: 'info'});
        } else {
            var txt = '<ul class="alert alert-info list-unstyled alert-dismissible"><button type="button" class="close" data-dismiss="alert" >&times;</button>';
            $.each(json.data,function(index, value) {
                txt += '<li>#' +value.id+ ' &#9;&#9;<a href="'+value.url+'" target="_blank" class="alert-link">'+value.title +'</a></li>';
            });
            $('#searchDouble').html(txt+'</ul>');
        }
    });
};
</script>

{{ includ_bb }}
