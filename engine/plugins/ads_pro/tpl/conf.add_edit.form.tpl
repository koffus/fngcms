<script language="javascript" type="text/javascript">
function AddBlok() {
	var tbl = document.getElementById('blokup');
	var lastRow = tbl.rows.length;
	var iteration = lastRow+1;
	var row = tbl.insertRow(lastRow);
	var cellRight = row.insertCell(0);
	cellRight.innerHTML = iteration+': ';
	cellRight = row.insertCell(1);
	cellRight.setAttribute('align', 'left');

	var el = '<select name="location[' + iteration + '][mode]" onchange="AddSubBlok(this, ' + iteration + ');"><option value=0>{{ lang['ads_pro:around'] }}</option><option value=1>{{ lang['ads_pro:main'] }}</option><option value=2>{{ lang['ads_pro:not_main'] }}</option><option value=3>{{ lang['ads_pro:category'] }}</option><option value=4>{{ lang['ads_pro:static'] }}</option>{% if flags.support_news %}<option value=5>{{ lang['ads_pro:news'] }}</option>{% endif %}<option value=6>{{ lang['ads_pro:plugins'] }}</option></select>';

	cellRight.innerHTML += el;
	
	el = '<select name="location[' + iteration + '][view]" class="form-control"><option value=0>{{ lang['ads_pro:view'] }}</option><option value=1>{{ lang['ads_pro:not_view'] }}</option></select>';
	
	cellRight.innerHTML += el;
}
function AddSubBlok(el, iteration){
	var subel = null;
	var subsubel = null;
	switch (el.value){
		case '3':
			subel = createNamedElement('select', 'location[' + iteration + '][id]');
			{{ category_list }}
			break;
		case '4':
			subel = createNamedElement('select', 'location[' + iteration + '][id]');
			{{ static_list }}
			break;
		{% if flags.support_news %}
			case '5':
				subel = createNamedElement('select', 'location[' + iteration + '][id]');
				{{ news_list }}
				break;
		{% endif %}
		case '6':
			subel = createNamedElement('select', 'location[' + iteration + '][id]');
			{{ plugins_list }}
			break;
	}
	if (el.nextSibling.name == 'location[' + iteration + '][id]')
		el.parentNode.removeChild(el.nextSibling);
	if (subel)
		el.parentNode.insertBefore(subel, el.nextSibling);
}
function RemoveBlok() {
	var tbl = document.getElementById('blokup');
	var lastRow = tbl.rows.length;
	if (lastRow > 0){
		tbl.deleteRow(lastRow - 1);
	}
}
function createNamedElement(type, name) {
 var element = null;
 try {
 element = document.createElement('<'+type+' name="'+name+'">');
 } catch (e) {
 }
 if (!element || element.nodeName != type.toUpperCase()) {
 element = document.createElement(type);
 element.setAttribute("name", name);
 }
 return element;
}
</script>

<form method="post" action="admin.php?mod=extra-config&amp;plugin=ads_pro&amp;action={% if flags.add %}add_submit{% endif %}{% if flags.edit %}edit_submit{% endif %}">
	<input type="hidden" name="id" value="{% if flags.add %}0{% endif %}{% if flags.edit %}{{ id }}{% endif %}" />

	<fieldset>
		<legend>{{ lang['ads_pro:general'] }}</legend>
		<div class="form-group">
			<div class="row">
				<div class="col-sm-8">
					{{ lang['ads_pro:name'] }}
					<span class="help-block">{{ lang['ads_pro:name#desc'] }}</span>
				</div>
				<div class="col-sm-4">
					<input type="text" class="form-control" name="name"{% if flags.edit %} value="{{ name }}"{% endif %} />
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-sm-8">
					{{ lang['ads_pro:description'] }}
					<span class="help-block">{{ lang['ads_pro:description#desc'] }}</span>
				</div>
				<div class="col-sm-4">
					<input type="text" class="form-control" name="description"{% if flags.edit %} value="{{ description }}"{% endif %} />
				</div>
			</div>
		</div>
		
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
		<td width="50%" class="contentEntry1">{{ lang['ads_pro:type'] }}<br /><small>{{ lang['ads_pro:type#desc'] }}</small></td>
		<td width="50%" class="contentEntry2">{{ type_list }}</td>
		</tr>
		<tr>
		<td width="50%" class="contentEntry1">{{ lang['ads_pro:location'] }}<br /><small>{{ lang['ads_pro:location#desc'] }}</small></td>
		<td width="50%" class="contentEntry2"><input type="button" class="button" value="{{ lang['ads_pro:location_dell'] }}" onClick="RemoveBlok();return false;" />&nbsp;
		<input type="button" class="button" value="{{ lang['ads_pro:location_add'] }}" onClick="AddBlok();return false;" /><br />
		<table id="blokup" align="left">{% if flags.edit %}{{ location_list }}{% endif %}</table>
		</td>
		</tr>
		<tr>
		<td width="50%" class="contentEntry1">{{ lang['ads_pro:state'] }}<br /><small>{{ lang['ads_pro:state#desc'] }}</small></td>
		<td width="50%" class="contentEntry2">{{ state_list }}</td>
		</tr>
		</table>
	</fieldset>

	<fieldset>
	<legend>{{ lang['ads_pro:sched_legend'] }}</legend>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
	<td width="50%" class="contentEntry1">{{ lang['ads_pro:start_view'] }}<br /><small>{{ lang['ads_pro:start_view#desc'] }}</small></td>
	<td width="50%" class="contentEntry2"><input type="text" class="form-control" name="start_view"{% if flags.edit %} value="{{ start_view }}"{% endif %} /></td>
	</tr>
	<tr>
	<td width="50%" class="contentEntry1">{{ lang['ads_pro:end_view'] }}<br /><small>{{ lang['ads_pro:end_view#desc'] }}</small></td>
	<td width="50%" class="contentEntry2"><input type="text" class="form-control" name="end_view"{% if flags.edit %} value="{{ end_view }}"{% endif %} /></td>
	</tr>
	</table>
	</fieldset><br />
	<fieldset>
	<legend>{{ lang['ads_pro:ads_blok_legend'] }}</legend>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
	<td width="100%" class="contentEntry1" align="center">
	<div style="width:100%; text-align: left;">{{ lang['ads_pro:ads_blok_info'] }}</div>
	<TEXTAREA NAME="ads_blok" COLS="150" ROWS="30">{% if flags.edit %}{{ ads_blok }}{% endif %}</TEXTAREA>
	</td>
	</tr>
	</table>
	</fieldset>


	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="100%" colspan="2">&nbsp;</td></tr>
	<tr>
	<td width="100%" colspan="2" class="contentEdit" align="center">
	<input type="submit" value="{% if flags.add %}{{ lang['ads_pro:add_submit'] }}{% endif %}{% if flags.edit %}{{ lang['ads_pro:edit_submit'] }}{% endif %}" class="button" />
	</td>
	</tr>
	</table>
</form>
