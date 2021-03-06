<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=news">{{ lang.news['news_title'] }}</a></li>
	<li class="active">{{ lang.news['editnews_title'] }} <b>{{ title }}</b></li>
</ul>

<!-- Info content -->
<div class="page-main">
	<!-- Main content form -->
	<form name="form" id="postForm" action="admin.php?mod=news" method="post" enctype="multipart/form-data" target="_self" class="form-horizontal">
		<input type="hidden" name="token" value="{{ token }}" />
		<input type="hidden" name="mod" value="news" />
		<input type="hidden" name="action" value="edit" />
		<input type="hidden" name="subaction" value="submit" />
		<input type="hidden" name="id" value="{{ id }}" />

		<div class="row">
			<div class="col col-sm-8">
				<!-- MAIN CONTENT -->
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title"><i class="fa fa-th-list"></i> {{ lang['maincontent'] }}</h4>
					</div>
					<div id="maincontent" class="panel-body">
						<div class="form-group">
							<label class="col-sm-3 control-label">{{ lang.news['title'] }}</label>
							<div class="col-sm-9">
								<div class="input-group">
									<input type="text" name="title" id="newsTitle" value="{{ title }}" tabindex="1" class="form-control"/>
									<span class="input-group-btn">
										<button type="button" onclick="searchDouble();" class="btn btn-default" title="Поиск дубликатов"><i class="fa fa-files-o"></i></button>
									</span>
								</div>
								<div id="searchDouble"></div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{ lang.news['alt_name'] }}</label>
							<div class="col-sm-9">
								<input type="text" name="alt_name" value="{{ alt_name }}" tabindex="2" class="form-control"{% if flags['altname.disabled'] %} disabled {% endif %}/>
							</div>
						</div>
						{% if (approve == 1) %}
						<div class="form-group">
							<label class="col-sm-3 control-label">{{ lang['url_news_page'] }}</label>
							<div class="col-sm-9">
								<div class="input-group">
									<input type="text" value="{{ link }}" tabindex="3" class="form-control" readonly />
									<span class="input-group-btn">
										<a href="{{ link }}" target="_blank" class="btn btn-default"><i class="fa fa-external-link"></i></a>
									</span>
								</div>
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
						<div class="form-group" id="fullwidth">
							<div class="col-sm-12">
								{% if (not isBBCode) %}
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
								<textarea name="ng_news_content" {% if (isBBCode) %}class="{{ attributBB }} form-control"{% else %}id="ng_news_content" class="form-control"{% endif %} rows="10" tabindex="5">{{ content }}</textarea>
							</div>
						</div>
						{% if (flags.meta) %}
						<div class="form-group">
							<label class="col-sm-3 control-label">{{ lang.news['description'] }}</label>
							<div class="col-sm-9">
								<textarea name="description" class="form-control" rows="4" tabindex="6">{{ description }}</textarea>
							</div>
						</div>
						<div id="form-keywords" class="form-group">
							<label class="col-sm-3 control-label">{{ lang.news['keywords'] }}</label>
							<div class="col-sm-9">
								<input type="text" name="keywords" id="newsKeywords" value="{{ keywords }}" tabindex="7" class="form-control" maxlength="255" />
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
								<a href="#attaches" data-toggle="collapse" data-parent="#accordion">{{ lang.news['bar.attaches'] }} {% if (attachCount>0) %}({{ attachCount }}){% endif %}</a>
							</h4>
						</div>
						<div id="attaches" class="panel-collapse collapse">
							<div class="panel-body">
								<table id="attachFilelist_edit" class="table table-condensed">
									<thead>
										<tr>
											<th>ID</th>
											<th><i class="fa fa-paperclip fa-2x"></i></th>
											<th>{{ lang.news['attach.filename'] }}</th>
											<th>{{ lang.news['attach.size'] }}</th>
											<th>{{ lang.news['attach.date'] }}</th>
											<th class="text-center">{{ lang['action'] }}</th>
										</tr>
									</thead>
									<tbody>
									{% for entry in attachEntries %}
										<tr>
											<td>{{ entry.id }}</td>
											<td>
												<a href="#" onclick="insertext('[attach#{{ entry.id }}]{{ entry.orig_name }}[/attach]','', currentInputAreaID); return false;" title="{{ lang['tags.file'] }}"><i class="fa fa-paperclip fa-2x"></i></a>
											</td>
											<td><a href="{{ entry.url }}">{{ entry.orig_name }}</a></td>
											<td>{{ entry.filesize }}</td>
											<td>{{ entry.date }}</td>
											<td class="text-center"><input type="checkbox" name="delfile_{{ entry.id }}" value="1" /></td>
										</tr>
									{% else %}
										<tr><td colspan="5">{{ lang.news['attach.no_files_attached'] }}</td></tr>
									{% endfor %}
										<tr>
											<td colspan="5"></td>
											<td class="text-center" width="10">
                                                <button type="button" title="{{ lang['attach.more_rows'] }}" onclick="attachAddRow('attachFilelist_edit');" class="btn btn-primary" title="{{ lang['attach.more_rows'] }}"><i class="fa fa-plus"></i></button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

				<!-- SUBMIT Form -->
				{% if flags['params.lost'] %}
				<div class="panel panel-danger">
					<div class="panel-heading">
						<h4 class="panel-title">Обратите внимание - у вас недостаточно прав для полноценного редактирования новости.</h4>
					</div>
					<div class="panel-body">
						<p>При сохранении будут произведены следующие изменения:</p>
						<ul>
							{% if flags['publish.lost'] %}<li>Новость будет снята с публикации</li>{% endif %}
							{% if flags['html.lost'] %}<li>В новости будет запрещено использование HTML тегов и автоформатирование</li>{% endif %}
							{% if flags['mainpage.lost'] %}<li>Новость будет убрана с главной страницы</li>{% endif %}
							{% if flags['pinned.lost'] %}<li>С новости будет снято прикрепление на главной</li>{% endif %}
							{% if flags['catpinned.lost'] %}<li>С новости будет снято прикрепление в категории</li>{% endif %}
							{% if flags['favorite.lost'] %}<li>Новость будет удалена из закладок администратора</li>{% endif %}
							{% if flags['multicat.lost'] %}<li>Из новости будут удалены все дополнительные категории</li>{% endif %}
						</ul>
					</div>
				</div>
				{% endif %}
				<div class="well panel-fixed-bottom">
					<div class="row">
						<div class="col col-xs-6">
							<select name="approve" id="approve" class="form-control">
								{% if flags.can_publish %}
									<option value="1" {% if (approve == 1) %}selected="selected"{% endif %}>{{ lang.news['publish'] }}</option>
								{% endif %}
								{% if flags.can_unpublish %}
									<option value="0" {% if (approve == 0) %}selected="selected"{% endif %}>{{ lang.news['send_moderation'] }}</option>
								{% endif %}
								{% if flags.can_draft %}
									<option value="-1" {% if (approve == -1) %}selected="selected"{% endif %}>{{ lang.news['save_draft'] }}</option>
								{% endif %}
							</select>
						</div>
						<div class="col col-xs-6 text-right">
							{% if flags.editable %}
								<button type="submit" title="Ctrl+S {{ lang.news['do_editnews'] }}" class="btn btn-success">
									<span class="visible-sm-block visible-xs-block"><i class="fa fa-floppy-o"></i></span>
									<span class="hidden-sm hidden-xs">{{ lang.news['do_editnews'] }}</span>
								</button>
							{% endif %}
							<button type="button" onClick="return preview();" title="{{ lang.news['preview'] }}" class="btn btn-primary"><i class="fa fa-eye"></i></button>
							{% if flags.deleteable %}
								<button type="button" onClick="confirmIt('admin.php?mod=news&amp;action=manage&amp;subaction=mass_delete&amp;selected_news[]={{ id }}&amp;token={{ token }}', '{{ lang['sure_del'] }}')" title="{{ lang.news['delete'] }}" class="btn btn-danger"><i class="fa fa-trash-o"></i></button>
							{% endif %}
						</div>
					</div>
				</div>
			</div>

			<!-- Right edit column -->
			<div id="rightBar" class="col col-sm-4">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">{{ lang['editor.comminfo'] }}</h4>
					</div>
					<table class="table table-condensed">
						<tbody>
							<tr>
								<td>{{ lang['editor.author'] }}</td>
								<td>
									<b><span id="news-author">{{ author }}</span></b>
									<div class="pull-right">
									{% if (pluginIsActive('uprofile')) %}
									<a href="{{ author_page }}" target="_blank" title="{{ lang.news['site.viewuser'] }}" class="btn-sm btn-default"><i class="fa fa-eye"></i></a>&nbsp;
									{% endif %}
									<a href="admin.php?mod=users&amp;action=editForm&amp;id={{ authorid }}" target="_blank" class="btn-sm btn-default"><i class="fa fa-pencil"></i></a>&nbsp;
									</div>
								</td>
							</tr>
							<tr>
								<td>{{ lang['editor.postdate'] }}</td>
								<td>{{ postdateStamp|cdate }}</td>
							</tr>
							{% if editdate %}<tr>
								<td>{{ lang['editor.editdate'] }}</td>
								<td>{{ editdateStamp|cdate }}</td>
							</tr>{% endif %}
							<tr>
								<td>{{ lang['state'] }}</td>
								<td><b>{% if (approve == -1) %}<span class="text-danger">{{ lang.news['state.draft'] }}</span>{% elseif (approve == 0) %}<span class="text-danger">{{ lang.news['state.unpublished'] }}</span>{% else %}<span class="text-success">{{ lang.news['state.published'] }}{% endif %}</span></b>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
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
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">{{ lang['editor.configuration'] }}</h4>
					</div>
					<div class="panel-body">
						<label><input type="checkbox" name="mainpage" value="1" {% if (flags.mainpage) %}checked="checked"{% endif %} id="mainpage" {% if (flags['mainpage.disabled']) %}disabled{% endif %} /> {{ lang.news['mainpage'] }}</label><br />
						<label><input type="checkbox" name="pinned" value="1" {% if (flags.pinned) %}checked="checked"{% endif %} id="pinned" {% if (flags['pinned.disabled']) %}disabled{% endif %} /> {{ lang.news['add_pinned'] }}</label><br />
						<label><input type="checkbox" name="catpinned" value="1" {% if (flags.catpinned) %}checked="checked"{% endif %} id="catpinned" {% if (flags['catpinned.disabled']) %}disabled{% endif %} /> {{ lang.news['add_catpinned'] }}</label><br />
						<label><input type="checkbox" name="favorite" value="1" {% if (flags.favorite) %}checked="checked"{% endif %} id="favorite" {% if (flags['favorite.disabled']) %}disabled{% endif %} /> {{ lang.news['add_favorite'] }}</label><br />
						<label><input name="flag_HTML" type="checkbox" id="flag_HTML" value="1" {% if (flags.html) %}checked="checked"{% endif %} {% if (flags['html.disabled']) %}disabled{% endif %} /> {{ lang.news['flag_html'] }}</label><br />
						<label><input type="checkbox" name="flag_RAW" value="1" {% if (flags.raw) %}checked="checked"{% endif %} id="flag_RAW" {% if (flags['html.disabled']) %}disabled{% endif %} /> {{ lang.news['flag_raw'] }}</label> {% if (flags['raw.disabled']) %}[<font color=red>{{ lang.news['flags_lost'] }}</font>]{% endif %}
					</div>
				</div>

				{% if not flags['customdate.disabled'] %}
				<div class="panel panel-default">
					<div class="panel-heading">{{ lang.news['date.manage'] }}</div>
					<div class="panel-body">
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="setdate_current" id="setdate_current" value="1" onclick="document.getElementById('setdate_custom').checked=false;" />
							</span>
							<input type="text" value="{{ lang.news['date.setcurrent'] }}" class="form-control"/>
						</div>
						&nbsp;
						<div class="input-group" title="{{ lang.news['date.setdate'] }}">
							<span class="input-group-addon">
								<input type="checkbox" name="setdate_custom" id="setdate_custom" value="1" onclick="document.getElementById('setdate_current').checked=false;" />
							</span>
							<input type="text" id="postdate" name="postdate" value="{{ postdate }}" class="form-control"/>
						</div>
					</div>
				</div>
				{% endif %}
				<div class="panel panel-default">
					<div class="panel-heading">{{ lang.news['set_views'] }}</div>
					<div class="panel-body">
						<div class="input-group">
							<span class="input-group-addon">
								<input type="checkbox" name="setViews" value="1" id="setViews" {% if (flags['setviews.disabled']) %}disabled{% endif %} />
							</span>
							<input type="text" name="views" value="{{ views }}" {% if (flags['setviews.disabled']) %}disabled{% endif %}class="form-control" />
						</div>
					</div>
				</div>
				{% if (pluginIsActive('comments')) %}
				<div class="panel panel-default">
					<div class="panel-heading">{{ lang['comments:mode.header'] }}</div>
					<div class="panel-body">
						<select name="allow_com" class="form-control">
							<option value="0"{{ plugin.comments['acom:0'] }}>{{ lang['comments:mode.disallow'] }}
							<option value="1"{{ plugin.comments['acom:1'] }}>{{ lang['comments:mode.allow'] }}
							<option value="2"{{ plugin.comments['acom:2'] }}>{{ lang['comments:mode.default'] }}
						</select>
					</div>
				</div>
				{% endif %}
			</div>
		</div>
	</form>

	<!-- COMMENTS -->
	{% if (pluginIsActive('comments')) %}
	<form name="commentsForm" id="commentsForm" action="admin.php?mod=news" method="post">
		<input type="hidden" name="token" value="{{ token }}" />
		<input type="hidden" name="action" value="edit" />
		<input type="hidden" name="subaction" value="mass_com_delete" />
		<input type="hidden" name="id" value="{{ id }}" />

		<div class="row">
			<div class="col col-sm-8">
				<!-- MAIN CONTENT -->
				<div id="comments" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title"> {{ lang.news['bar.comments'] }} ({% if plugin.comments.count > 0 %}{{ plugin.comments.count }}{% else %}{{ lang['noa'] }}{% endif %})</h4>
					</div>
					{% if plugin.comments.count > 0 %}
					<table class="table table-">
					<tbody>
						<tr>
							<td colspan="2" class="text-right"><input type="checkbox" name="master_box" value="all" class="select-all" /></td>
						</tr>
						{{ plugin.comments.list }}
					</tbody>
					</table>
					<div class="panel-footer text-right">
						<button type="submit" title="{{ lang.news['comdelete'] }}" onClick="if (!confirm('{{ lang['sure_del'] }}')) {return false;}" class="btn btn-danger"><i class="fa fa-trash-o"></i></button>
					</div>
					{% endif %}
				</div>
			</div>
		</div>
	</form>
	{% endif %}
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

<link href=" {{ scriptLibrary }}/datetimepicker-4.15.35/datetimepicker.css" rel="stylesheet">
<script src="{{ scriptLibrary }}/js/moment-2.17.1.js"></script>
<script src="{{ scriptLibrary }}/datetimepicker-4.15.35/datetimepicker.js"></script>

<script>
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
insertselcat();

$('#postdate').datetimepicker({format:'DD.MM.YYYY HH:mm',locale: "{{ lang['langcode'] }}"});

//
// Global variable: ID of current active input area
var currentInputAreaID = 'ng_news_content';
var form = document.getElementById('postForm');

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
attachAddRow('attachFilelist_edit');

// HotKeys to this page
document.onkeydown = function(e) {
	e = e || event;
	if (e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
		form.submit();
		return false;
	}
}

</script>

<script>
var searchDouble = function() {
    if ($.trim($('#newsTitle').val()).length < 4)
        return $.notify({message: '{{ lang.news['msge_title'] }}'},{type: 'danger'});
    var url = '{{ admin_url }}/rpc.php';
    var method = 'admin.news.double';
    var params = {'token': '{{ token }}','title': $('#newsTitle').val(),'news_id': '{{ id }}','mode': 'edit',};
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
