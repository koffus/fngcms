<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=news">{{ lang['news_title'] }}</a></li>
	<li class="active">{{ lang.addnews['addnews_title'] }}</li>
</ul>

<script src="{{ home }}/lib/ajax.js"></script>
<script src="{{ home }}/lib/libsuggest.js"></script>

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
								{{ lang.addnews['title'] }}
								<!--span class="label label-info pull-right" title="Заголовок новости">?</span-->
								</label>
							<div class="col-sm-9 has-feedback">
								<input type="text" name="title" id="newsTitle" value="" tabindex="1" class="form-control"/>
								<!--i class="fa fa-user form-control-feedback" title="Заголовок новости:"></i-->
							</div>
						</div>
						{% if not flags['altname.disabled'] %}
						<div class="form-group">
							<label class="col-sm-3 control-label">{{ lang.addnews['alt_name'] }}</label>
							<div class="col-sm-9">
								<input type="text" name="alt_name" value="" tabindex="2" class="form-control"/>
							</div>
						</div>
						{% endif %}
						<div class="form-group">
							<label class="col-sm-3 control-label">
								{{ lang.editnews['category'] }}
								{% if (flags.mondatory_cat) %} <span class="text-danger"><b>*</b></span>{% endif %}
							</label>
							<div class="col-sm-9">{{ mastercat }}</div>
						</div>
						<div id="fullwidth" class="form-group">
							<div class="col-sm-12">
								{% if (not isBBCode) %}
									{{ quicktags }}
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
								{% else %}
								{% endif %}
								{% if (flags.edit_split) %}
									<div id="container.content.short" class="contentActive">
										<textarea onclick="changeActive('short');" onfocus="changeActive('short');" name="ng_news_content_short" {% if (isBBCode) %}class="{{ attributBB }} form-control"{% else %}id="ng_news_content_short" class="form-control"{% endif %} rows="4" tabindex="3"></textarea>
									</div>
									{% if (flags.extended_more) %}
									<div class="form-group">
										<label class="col-sm-3 control-label">{{ lang.addnews['editor.divider'] }}</label>
										<div class="col-sm-9">
											<input type="text" name="content_delimiter" value="" tabindex="4" class="form-control"/>
										</div>
									</div>
									{% endif %}
									<div id="container.content.full" class="contentInactive">
										<textarea onclick="changeActive('full');" onfocus="changeActive('full');" name="ng_news_content_full" {% if (isBBCode) %}class="{{ attributBB }} form-control"{% else %}id="ng_news_content_full" class="form-control"{% endif %} rows="10" tabindex="5"></textarea>
									</div>
								{% else %}
									<div id="container.content" class="contentActive">
										<textarea name="ng_news_content" {% if (isBBCode) %}class="{{ attributBB }} form-control"{% else %}id="ng_news_content" class="form-control ng_news_content"{% endif %} rows="10" tabindex="5"></textarea>
									</div>
								{% endif %}
							</div>
						</div>
						{% if (flags.meta) %}
						<div class="form-group">
							<label class="col-sm-3 control-label">{{ lang.addnews['description'] }}</label>
							<div class="col-sm-9">
								<textarea name="description" class="form-control" rows="4" tabindex="6"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">
								{{ lang.addnews['keywords'] }}
								<i class="fa fa-question-circle" title="Указываются через запятую, при автозаполнении составляется из содержания новости"></i>
								<i class="fa fa-refresh" title="Заполнить автоматически" onclick="countKeywords();/*$('#keywords').val($('#title').val().split(' ').join(',').toLowerCase());*/"></i> 
								<i class="fr fa fa-cog" onclick="$('.c2').toggle();" title="Настроить генерацию ключевых слов"></i>
							</label>
							<div class="col-sm-9">
								<input type="text" name="keywords" id="newsKeywords" value="" tabindex="7" class="form-control" maxlength="255" />
							</div>
						</div>
						<div class="c2 well" style="display: none">
							<div class="row">
								<div class="col-sm-offset-3 col-sm-9">
									<div class="form-group">
										<label class="col-sm-5 control-label">Минимальная длина слова</label>
										<div class="col-sm-7">
											<input type="number" value="5" id="minLengthKeyword" class="form-control" />
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-5 control-label">Минимальное число повторений</label>
										<div class="col-sm-7">
											<input type="number" value="3" id="minRepeatKeyword" class="form-control" />
										</div>
									</div>
									<div class="row">
										<label class="col-sm-5 control-label">Коэффициент совпадения</label>
										<div class="col-sm-7">
											<input type="number" value="0.7" id="coincidence" class="form-control" />
										</div>
									</div>
								</div>
							</div>
						</div>
						{% endif %}
						{% if (pluginIsActive('comments')) %}
						<div class="form-group">
							<label class="col-sm-3 control-label">{{ lang['comments:mode.header'] }}</label>
							<div class="col-sm-9">
								<select name="allow_com" class="form-control">
									<option value="0"{{ plugin.comments['acom:0'] }}>{{ lang['comments:mode.disallow'] }}
									<option value="1"{{ plugin.comments['acom:1'] }}>{{ lang['comments:mode.allow'] }}
									<option value="2"{{ plugin.comments['acom:2'] }}>{{ lang['comments:mode.default'] }}
								</select>
							</div>
						</div>
						{% endif %}
					</div>
					{% if (pluginIsActive('xfields') and plugin.xfields[1]) %}
					<!-- XFields -->
					<table class="table table-condensed">
						{{ plugin.xfields[1] }}
					</table>
					<!-- /XFields -->
					{% endif %}
				</div>
				
				<div class="panel-group" id="accordion">
					<!-- ADDITIONAL -->
					<div class="panel panel-default panel-table">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-th-list"></i> 
								<a href="#additional" data-toggle="collapse" data-parent="#accordion" aria-expanded="false">{{ lang.addnews['bar.additional'] }}</a>
							</h4>
						</div>
						<div id="additional" class="panel-collapse collapse" aria-expanded="false">
							<div class="panel-body">
								<table class="table table-condensed">
									<tbody>
										<tr>
										{% if (pluginIsActive('tags')) %}{{ plugin.tags }}{% endif %}
										{% if (pluginIsActive('xfields') and plugin.xfields[0]) %}{{ plugin.xfields[0] }}{% endif %}
										{% if (pluginIsActive('nsched')) %}{{ plugin.nsched }}{% endif %}
										{% if (pluginIsActive('finance')) %}{{ plugin.finance }}{% endif %}
										{% if (pluginIsActive('tracker')) %}{{ plugin.tracker }}{% endif %}
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
					<!-- ATTACHES -->
					<div class="panel panel-default panel-table">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-th-list"></i> 
								<a href="#attaches" data-toggle="collapse" data-parent="#accordion" aria-expanded="false">{{ lang.addnews['bar.attaches'] }}</a>
							</h4>
						</div>
						<div id="attaches" class="panel-collapse collapse" aria-expanded="false">
							<div class="panel-body">
								<table id="attachFilelist" class="table table-condensed table-bordered">
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
												<button type="button" title="{l_attach.more_rows}" onclick="attachAddRow('attachFilelist');" class="btn btn-primary" title="{l_attach.more_rows}"><i class="fa fa-plus"></i></button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
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
								{{ lang.addnews['mainpage'] }}
							</label>
							<br />
							<label>
								<input type="checkbox" name="pinned" value="1" id="pinned" {% if (flags.pinned) %}checked="checked" {% endif %}{% if flags['pinned.disabled'] %}disabled {% endif %} />&nbsp;
								{{ lang.addnews['add_pinned'] }}
							</label>
							<br />
							<label>
								<input type="checkbox" name="catpinned" value="1" id="catpinned" {% if (flags.catpinned) %}checked="checked" {% endif %}{% if flags['catpinned.disabled'] %}disabled {% endif %} />&nbsp;
								{{ lang.addnews['add_catpinned'] }}
							</label>
							<br />
							<label>
								<input type="checkbox" name="favorite" value="1" id="favorite" {% if (flags.favorite) %}checked="checked" {% endif %}{% if flags['favorite.disabled'] %}disabled {% endif %} />&nbsp;
								{{ lang.addnews['add_favorite'] }}
							</label>
							<br />
							<label>
								<input name="flag_HTML" type="checkbox" id="flag_HTML" value="1" {% if (flags['html.disabled']) %}disabled {% endif %} {% if (flags['html']) %}checked="checked"{% endif %}/>&nbsp;
								{{ lang.addnews['flag_html'] }}
							</label>
							<br />
							<label>
								<input type="checkbox" name="flag_RAW" value="1" id="flag_RAW" {% if (flags['html.disabled']) %}disabled {% endif %} {% if (flags['raw']) %}checked="checked"{% endif %}/>&nbsp;
								{{ lang.addnews['flag_raw'] }}
							</label>
					</div>
				</div>
				{% if not flags['customdate.disabled'] %}
				<div class="panel panel-default">
					<div class="panel-heading">{{ lang.addnews['custom_date'] }}</div>
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
			</div>
		</div>
		
		<div class="row">
			<div class="col col-sm-12">
				{% if (pluginIsActive('xfields') and plugin.xfields.general) %}
				<!-- XFields [GENERAL] -->
				<table class="table table-condensed">
					{{ plugin.xfields.general }}
				</table>
				<!-- /XFields [GENERAL] -->
				{% endif %}
			</div>
		</div>
		
		<div class="row">
			<div class="col col-sm-8">
				<div class="well panel-fixed-bottom">
					<div class="row">
						<div class="col col-xs-6">
							<select name="approve" class="form-control">
								{% if flags['can_publish'] %}
									<option value="1">{{ lang.addnews['publish'] }}</option>
								{% endif %}
								<option value="0">{{ lang.addnews['send_moderation'] }}</option>
								<option value="-1">{{ lang.addnews['save_draft'] }}</option>
							</select>
						</div>
						<div class="col col-xs-6 text-right">
							<button type="submit" title="Ctrl+S {{ lang.addnews['publish'] }}" class="btn btn-success">
								<span class="visible-sm-block visible-xs-block"><i class="fa fa-floppy-o"></i></span>
								<span class="hidden-sm hidden-xs">{{ lang['add'] }}</span>
							</button>
							<button type="button" title="{{ lang.addnews['preview'] }}" onclick="return preview();" class="btn btn-primary"><i class="fa fa-eye"></i></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<form name="DATA_tmp_storage" action="" id="DATA_tmp_storage"><input type="hidden" name="area" value="" /></form>

<link href="{{ skins_url }}/assets/css/datetimepicker.css" rel="stylesheet">
<script src="{{ skins_url }}/assets/js/moment.js"></script>
<script src="{{ skins_url }}/assets/js/datetimepicker.js"></script>

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
//insertselcat();

$('#cdate').datetimepicker({format:'DD.MM.YYYY HH:mm',locale: "{{ lang['langcode'] }}"});

//
// Global variable: ID of current active input area
var currentInputAreaID = 'ng_news_content{% if (flags.edit_split) %}_short{% endif %}';
var form = document.getElementById('postForm');

function preview(){
	
	if (form.ng_news_content{% if (flags.edit_split) %}_short{% endif %}.value == '' || form.title.value == '') {
		$.notify({message: '{{ lang.addnews['msge_preview'] }}'},{type: 'danger'});
		return false;
	}
	
	form['mod'].value = "preview";
	form.target = "_blank";
	form.submit();

	form['mod'].value = "news";
	form.target = "_self";
	return true;
}

function changeActive(name) {
 if (name == 'full') {
	document.getElementById('container.content.full').className = 'contentActive';
	document.getElementById('container.content.short').className = 'contentInactive';
	currentInputAreaID = 'ng_news_content_full';
 } else {
	document.getElementById('container.content.short').className = 'contentActive';
	document.getElementById('container.content.full').className = 'contentInactive';
	currentInputAreaID = 'ng_news_content_short';
 }
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

{{ includ_bb }}

<script type="text/javascript">
  var keywords1, keywords2 = new Array(), keywords3 =  new Array(), del_symbols;
  
  function getKeywords(s) {
    var tmp;
    tmp = s.toLowerCase().replace(/<[^>]+>/g,'').replace(/[^а-яА-Яa-zA-Z]+/g, ' ').replace(/\[.*?\].*?\[.*?\]/gi, '');
    return tmp.split(' ');
  }
  function countKeywords () {
    var s = $('#ng_news_content').val()+$('#title').val()+$('#description').val();
	var minLengthKeyword = parseInt($('#minLengthKeyword').val());
	var minRepeatKeyword = parseInt($('#minRepeatKeyword').val());
	var coincidence = parseFloat($('#coincidence').val());
	
	var tmpKeywords1 = getKeywords(s);
	var tmpKeywords2 = new Array();
    
	for (i=0;i<tmpKeywords1.length;i++) {
      var currentWord = tmpKeywords1[i];
      if (currentWord.length >= minLengthKeyword) keywords2.push(currentWord);
    }
    
    for (i=0;i<keywords2.length;i++) {
      var currentWord = keywords2[i];
      currentWordCore = currentWord.substr(0,Math.round(currentWord.length*coincidence));
      
      var inwords2 = keywords2.grep(currentWordCore);
      if (inwords2.length >= minRepeatKeyword && keywords3.grep(currentWordCore).length <1) {
        keywords3.push(currentWord);
      }
    }
    
    $('#newsKeywords').val(keywords3);
    keywords2 = new Array();
    keywords3 = new Array();
  }
  
  function grep(str) {
    var ar = new Array();
    var arSub = 0;
    for (var i in this) {
      if (typeof this[i] == "string" && this[i].indexOf(str) != -1){
        ar[arSub] = this[i];
        arSub++;
      }
    }
    return ar;
  }
  
  Array.prototype.remove=function(s) {
    for(i=0;i<this .length;i++) {
      if(s==this[i]) this.splice(i, 1);
    }
  }
  
  Array.prototype.grep = grep;
</script>