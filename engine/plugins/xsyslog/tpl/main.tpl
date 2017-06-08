<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
	<li class="active">xsyslog [Журнал действий пользователей]</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<!-- Filter form: BEGIN -->
	<form name="options_bar" action="admin.php?mod=extra-config&plugin=xsyslog" method="post">
		<div id="filter" class="collapse" aria-expanded="true">
			<div class="well">
				<div class="row">
					<!--Block 1-->
					<div class="col col-md-3 col-sm-6">
						<div class="form-group">
							<label>Пользователь</label>
							<input name="an" id="an" type="text" value="{{an}}" autocomplete="off" class="bfauthor form-control" /> <span id="suggestLoader" style="width: 20px; visibility: hidden;"><img src="{{skins_url}}/images/loading.gif"/></span>
						</div>
						<div class="form-group">
							<label>Статус</label>
							<select name="status" class="bfstatus form-control">
								<option value="null" {% if fstatus == 'null' %}selected{% endif %}>- Все -</option>
								<option value="0" {% if fstatus == '0' %}selected{% endif %}>0</option>
								<option value="1" {% if fstatus == '1' %}selected{% endif %}>1</option>
							</select>
						</div>
					</div>
					<!--/Block 1-->
					<!--Block 2-->
					<div class="col col-md-3 col-sm-6">
						<div class="form-group">
							<label class="left">Plugin</label>&nbsp;&nbsp;
							{{catPlugins}}
						</div>
						<div class="form-group">
							<label class="left">Item</label>&nbsp;&nbsp;
							{{catItems}}
						</div>
					</div>
					<!--/Block 2-->
					<!--Block 3-->
					<div class="col col-md-3 col-sm-6">
						<div class="form-group">
							<label>Дата</label>
							<div class="input-group">
								<span class="input-group-addon">с&nbsp;&nbsp;&nbsp;</span>
								<input type="text" id="dr1" name="dr1" value="{{fDateStart}}" class="bfdate form-control"/>
							</div>
						</div>
						<div class="form-group">
							<label>Дата</label>
							<div class="input-group">
								<span class="input-group-addon">по</span>
								<input type="text" id="dr2" name="dr2" value="{{fDateEnd}}" class="bfdate form-control"/>
							</div>
						</div>
					</div>
					<!--/Block 3-->
					<!--Block 4-->
					<div class="col col-md-3 col-sm-6">
						<div class="form-group">
							<label>На странице</label>
							<input type="text" name="rpp" value="{{rpp}}" class="form-control" />
						</div>
						<div class="form-group">
							<label for="">&nbsp;</label>
							<button type="submit" class="filterbutton btn btn-primary form-control">Показать</button>
						</div>
					</div>
					<!--Block 4-->
				</div>
			</div>
		</div>
		<!-- Filter form: END -->

		<div class="panel panel-default panel-table">
			<div class="panel-heading text-right">
				<div class="btn-group">
					<button type="button" data-toggle="collapse" data-target="#filter" aria-expanded="true" aria-controls="filter" class="btn btn-default"><i class="fa fa-cog"></i></button>
				</div>
			</div>
			<div class="panel-body table-responsive">
				<table class="table table-condensed">
					<thead>
						<tr>
							<th>ID</th>
							<th>Data</th>
							<th>IP</th>
							<th>Plugin</th>
							<th>Item</th>
							<th>DS</th>
							<th>Action</th>
							<th>User</th>
							<th>Status</th>
							<th>Text</th>
						</tr>
					</thead>
					<tbody>
					{% for entry in entries %}
						<tr>
							<td>{{ entry.id }}</td>
							<td>{{ entry.date }}</td>
							<td>{{ entry.ip }}</td>
							<td>{{ entry.plugin }}</td>
							<td>{{ entry.item }}</td>
							<td>{{ entry.ds }}</td>
							<td>{{ entry.action }}</td>
							<td><a href="admin.php?mod=users&action=editForm&id={{ entry.userid }}" />{{ entry.username }}</a></td>
							<td>{{ entry.status }}</td>
							<td>{{ entry.stext }}</td>
						</tr>
					{% else %}
						<tr><td colspan="10"><p>По вашему запросу ничего не найдено.</p></td></tr>
					{% endfor %}
					</tbody>
				</table>
			</div>
			{% if pagesss %}
			<div class="panel-footer">
				<div class="row">
					<div class="col col-md-4"></div>
					<div class="col col-md-8 text-right">
						{{ pagesss }}
					</div>
				</div>
			</div>
			{% endif %}
		</div>
	</form>
</div>

<!-- Hidden SUGGEST div -->
<div id="suggestWindow" class="suggestWindow"><table id="suggestBlock" cellspacing="0" cellpadding="0" width="100%"></table><a href="#" align="right" id="suggestClose">{{ lang['close'] }}</a></div>
<script src="{{ scriptLibrary }}/ajax.js"></script>
<script src="{{ scriptLibrary }}/admin.js"></script>
<script src="{{ scriptLibrary }}/libsuggest.js"></script>

<link href="{{ skins_url }}/assets/css/datetimepicker.css" rel="stylesheet">
<script src="{{ skins_url }}/assets/js/moment.js"></script>
<script src="{{ skins_url }}/assets/js/datetimepicker.js"></script>

<script type="text/javascript">
<!--

function addEvent(elem, type, handler){
 if (elem.addEventListener){
 elem.addEventListener(type, handler, false)
 } else {
 elem.attachEvent("on"+type, handler)
 }
}

// DateEdit filter
function filter_attach_DateEdit(id) {
	var field = document.getElementById(id);
	if (!field)
		return false;

	if (field.value == '')
		field.value = 'DD.MM.YYYY';

	field.onfocus = function(event) {
		var ev = event ? event : window.event;
		var elem = ev.target ? ev.target : ev.srcElement;

		if (elem.value == 'DD.MM.YYYY')
			elem.value = '';

		return true;
	}


	field.onkeypress = function(event) {
		var ev = event ? event : window.event;
		var keyCode = ev.keyCode ? ev.keyCode : ev.charCode;
		var elem = ev.target ? ev.target : ev.srcElement;
		var elv = elem.value;

		isMozilla = false;
		isIE = false;
		isOpera = false;
		if (navigator.appName == 'Netscape') { isMozilla = true; }
		else if (navigator.appName == 'Microsoft Internet Explorer') { isIE = true; }
		else if (navigator.appName == 'Opera') { isOpera = true; }
		else { /* alert('Unknown navigator: `'+navigator.appName+'`'); */ }

		//document.getElementById('debugWin').innerHTML = 'keyPress('+ev.keyCode+':'+ev.charCode+')['+(ev.shiftKey?'S':'.')+(ev.ctrlKey?'C':'.')+(ev.altKey?'A':'.')+']<br/>' + document.getElementById('debugWin').innerHTML;

		// FF - onKeyPress captures functional keys. Skip anything with charCode = 0
		if (isMozilla && !ev.charCode)
			return true;

		// Opera - dumb browser, don't let us to determine some keys
		if (isOpera) {
			var ek = '';
			//for (i in event) { ek = ek + '['+i+']: '+event[i]+'<br/>\n'; }
			//alert(ek);
			if (ev.keyCode < 32) return true;
			if (!ev.shiftKey && ((ev.keyCode >= 33) && (ev.keyCode <= 47))) return true;
			if (!ev.keyCode) return true;
			if (!ev.which) return true;
		}


		// Don't block CTRL / ALT keys
		if (ev.altKey || ev.ctrlKey || !keyCode)
			return true;

		// Allow to input only digits [0..9] and dot [.]
		if (((keyCode >= 48) && (keyCode <= 57)) || (keyCode == 46))
			return true;

		return false;
	}

	return true;
}

-->
</script>
<script type="text/javascript">
$('#dr1').datetimepicker({format: 'DD.MM.YYYY', locale: 'ru',pickTime:false});
$('#dr2').datetimepicker({format: 'DD.MM.YYYY', locale: 'ru',pickTime:false});


<!--
// INIT NEW SUGGEST LIBRARY [ call only after full document load ]
function systemInit() {
var aSuggest = new ngSuggest('an',
								{
									'localPrefix'	: '{{ localPrefix }}',
									'reqMethodName'	: 'core.users.search',
									'lId'		: 'suggestLoader',
									'hlr'		: 'true',
									'iMinLen'	: 1,
									'stCols'	: 2,
									'stColsClass': [ 'cleft', 'cright' ],
									'stColsHLR'	: [ true, false ],
								}
							);

}

// Init system [ IE / Other browsers should be inited in different ways ]
if (document.body.attachEvent) {
	// IE
	document.body.onload = systemInit;
} else {
	// Others
	systemInit();
}

filter_attach_DateEdit('dr1');
filter_attach_DateEdit('dr2');
-->
</script>