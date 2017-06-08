<script>
function AddBlok() {
	var tbl = document.getElementById('blokup');
	var lastRow = tbl.rows.length;
	var iteration = lastRow+1;
	var row = tbl.insertRow(lastRow);
	var cellRight = row.insertCell(0);
	cellRight.setAttribute('class', 'location_' + iteration);
	cellRight.innerHTML = iteration+': ';
	cellRight = row.insertCell(1);
	cellRight.setAttribute('class', 'location_' + iteration);

	var el = '<select name="location[' + iteration + '][mode]" onchange="AddSubBlok(this, ' + iteration + ');" class="form-control">\
		<option value=0>{{ lang['ads_pro:around'] }}</option>\
		<option value=1>{{ lang['ads_pro:main'] }}</option>\
		<option value=2>{{ lang['ads_pro:not_main'] }}</option>\
		<option value=3>{{ lang['ads_pro:category'] }}</option>\
		<option value=4>{{ lang['ads_pro:static'] }}</option>\
		{% if flags.support_news %}<option value=5>{{ lang['ads_pro:news'] }}</option>{% endif %}\
		<option value=6>{{ lang['ads_pro:plugins'] }}</option>\
	</select>';

	cellRight.innerHTML += el;
	
	cellRight = row.insertCell(2);
	cellRight.setAttribute('class', 'location_' + iteration);

	cellRight = row.insertCell(3);
	cellRight.setAttribute('class', 'location_' + iteration);

	el = '<select name="location[' + iteration + '][view]" class="form-control">\
		<option value=0>{{ lang['ads_pro:view'] }}</option>\
		<option value=1>{{ lang['ads_pro:not_view'] }}</option>\
	</select>';

	cellRight.innerHTML += el;
}
function AddSubBlok(el, iteration) {
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
	$('.location_' + iteration).eq(2).html();
	$('.location_' + iteration).eq(2).html(subel);
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
		element.setAttribute('name', name);
		element.setAttribute('class', 'form-control');
		//element.setAttribute('id', name);
	}
	return element;
}
</script>

<form method="post" action="admin.php?mod=extra-config&plugin=ads_pro&action={% if flags.add %}add_submit{% endif %}{% if flags.edit %}edit_submit{% endif %}" class="form-horizontal">
	<input type="hidden" name="id" value="{% if flags.add %}0{% endif %}{% if flags.edit %}{{ id }}{% endif %}" />

	<fieldset>
		<legend>{{ lang['ads_pro:general'] }}</legend>
		<div class="form-group">
			<div class="col-sm-8">
				{{ lang['ads_pro:name'] }}
				<span class="help-block">{{ lang['ads_pro:name#desc'] }}</span>
			</div>
			<div class="col-sm-4">
				<input type="text" name="name"{% if flags.edit %} value="{{ name }}"{% endif %} class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-8">
				{{ lang['ads_pro:description'] }}
				<span class="help-block">{{ lang['ads_pro:description#desc'] }}</span>
			</div>
			<div class="col-sm-4">
				<input type="text" name="description"{% if flags.edit %} value="{{ description }}"{% endif %} class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-8">
				{{ lang['ads_pro:type'] }}
				<span class="help-block">{{ lang['ads_pro:type#desc'] }}</span>
			</div>
			<div class="col-sm-4">
				{{ type_list }}
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>{{ lang['ads_pro:state'] }}</legend>
		<div class="form-group">
			<div class="col-sm-8">
				{{ lang['ads_pro:state#desc'] }}
			</div>
			<div class="col-sm-4">
				{{ state_list }}
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-8">
				{{ lang['ads_pro:location'] }}
				<span class="help-block">{{ lang['ads_pro:location#desc'] }}</span>
			</div>
			<div class="col-sm-4">
				<div class="btn-group btn-group-justified">
				<a href="#" class="btn btn-default" onClick="AddBlok();return false;">{{ lang['ads_pro:location_add'] }}</a>
				<a href="#" class="btn btn-default" onClick="RemoveBlok();return false;">{{ lang['ads_pro:location_dell'] }}</a>
				</div>
			</div>
		</div>
		<table id="blokup" class="well table table-condensed"><tbody>{% if flags.edit %}{{ location_list }}{% endif %}</tbody></table>
	</fieldset>

	<fieldset>
		<legend>{{ lang['ads_pro:sched_legend'] }}</legend>
		<div class="form-group">
			<div class="col-sm-8">
				{{ lang['ads_pro:start_view'] }}
				<span class="help-block">{{ lang['ads_pro:start_view#desc'] }}</span>
			</div>
			<div class="col-sm-4">
				<input type="text" name="start_view"{% if flags.edit %} value="{{ start_view }}"{% endif %} class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-8">
				{{ lang['ads_pro:end_view'] }}
				<span class="help-block">{{ lang['ads_pro:end_view#desc'] }}</span>
			</div>
			<div class="col-sm-4">
				<input type="text" name="end_view"{% if flags.edit %} value="{{ end_view }}"{% endif %} class="form-control" />
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend>{{ lang['ads_pro:ads_blok_legend'] }}</legend>
		<div class="form-group">
			<div class="col-sm-12">
				<span class="help-block">{{ lang['ads_pro:ads_blok_info'] }}</span>
				<br>
				<textarea name="ads_blok" rows="15" class="form-control">{% if flags.edit %}{{ ads_blok }}{% endif %}</textarea>
			</div>
		</div>
	</fieldset>
	
	<div class="well text-center"><input type="submit" value="{% if flags.add %}{{ lang['ads_pro:add_submit'] }}{% endif %}{% if flags.edit %}{{ lang['ads_pro:edit_submit'] }}{% endif %}" class="btn btn-success" /></div>
</form>
