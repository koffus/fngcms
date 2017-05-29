<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
	<li><a href="admin.php?mod=extra-config&plugin=xfields&section={{ sectionID }}">{{ lang.xfconfig['config_text'] }} xfields</a></li>
	<li class="active">{% if (not flags.editMode) %}{{ lang.xfconfig['title_add'] }}{% else %}{{ lang.xfconfig['title_edit'] }} ({{ id }}){% endif %}</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<form action="admin.php?mod=extra-config&plugin=xfields" name="xfieldsform" method="post" class="form-horizontal">
		<input type="hidden" name="action" value="doedit" />
		<input type="hidden" name="section" value="{{ sectionID }}" />
		<input type="hidden" name="edit" value="{% if (flags.editMode) %}1{% else %}0{% endif %}" />

		<fieldset>
			<div class="alert alert-info">{{ lang.xfconfig['id_desc'] }}</div>
			<div class="form-group">
				<div class="col-sm-5">{{ lang.xfconfig['id'] }}</div>
				<div class="col-sm-7">
					<input type="text" name="id" value="{{ id }}" {% if (flags.editMode) %}readonly{% endif %} class="form-control" />
					{% if (flags.editMode) %}<span class="help-block">{{ lang.xfconfig['noeditid'] }}</span>{% endif %}
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-5">{{ lang.xfconfig['title'] }}</div>
				<div class="col-sm-7">
					<input type="text" name="title" value="{{ title }}" class="form-control" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-5">{{ lang.xfconfig['type'] }}</div>
				<div class="col-sm-7">
					<select name="type" id="xfSelectType" onclick="clx(this.value);" onchange="clx(this.value);" class="form-control">
						{{ type_opts }}
					</select>
				</div>
			</div>
		</fieldset>

		<!-- FIELD TYPE: TEXT -->
		<fieldset id="type_text">
			<div class="well">
				<div class="form-group">
					<label class="col-sm-5 control-label">{{ lang.xfconfig['default'] }}</label>
					<div class="col-sm-7">
						<input type="text" name="text_default" value="{{ defaults.text }}" class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-5 col-sm-7">
							<div class="checkbox">
							<label>
								<input type="checkbox" name="text_html_support" value="1" {{ html_support }} />
								{{ lang.xfconfig['html_support'] }}
							</label>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-offset-5 col-sm-7">
							<div class="checkbox">
							<label>
								<input type="checkbox" name="text_bb_support" value="1" {{ bb_support }} />
								{{ lang.xfconfig['bb_support'] }}
							</label>
						</div>
					</div>
				</div>
			</div>
		</fieldset>

		<!-- FIELD TYPE: TEXTAREA -->
		<fieldset id="type_textarea">
			<div class="well">
				<div class="form-group">
					<label class="col-sm-5 control-label">{{ lang.xfconfig['default'] }}</label>
					<div class="col-sm-7">
						<textarea name="textarea_default" rows="4" class="form-control">{{ defaults.textarea }}</textarea>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-5 col-sm-7">
							<div class="checkbox">
							<label>
								<input type="checkbox" name="textarea_html_support" value="1" {{ html_support }} />
								{{ lang.xfconfig['html_support'] }}
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-5 col-sm-7">
							<div class="checkbox">
							<label>
								<input type="checkbox" name="textarea_bb_support" value="1" {{ bb_support }} />
								{{ lang.xfconfig['bb_support'] }}
							</label>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-offset-5 col-sm-7">
							<div class="checkbox">
							<label>
								<input type="checkbox" name="textarea_noformat" value="1" {{ noformat }} />
								{{ lang.xfconfig['noformat'] }}
							</label>
						</div>
					</div>
				</div>
			</div>
		</fieldset>

		<!-- FIELD TYPE: SELECT -->
		<fieldset id="type_select">
			<div class="well">
				<div class="form-group">
					<label class="col-sm-5 control-label">
						{{ lang.xfconfig['tselect_default'] }}
						<span class="help-block">{{ lang.xfconfig['tselect_default_desc'] }}</span>
					</label>
					<div class="col-sm-7">
						<input type="text" name="select_default" value="{{ defaults.select }}" class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-5 control-label">
						{{ lang.xfconfig['tselect_storekeys'] }}
						<span class="help-block">{{ lang.xfconfig['tselect_storekeys_desc'] }}</span>
					</label>
					<div class="col-sm-7">
						<select name="select_storekeys" class="form-control">{{ storekeys_opts }}</select>
					</div>
				</div>
				<div class="row">
					<label class="col-sm-5 control-label">
						{{ lang.xfconfig['tselect_options'] }}
						<span class="help-block">{{ lang.xfconfig['tselect_options_desc'] }}</span>
					</label>
					<div class="col-sm-7">
						<table id="xfSelectTable" class="table-condensed table-bordered">
							<thead>
								<tr>
									<th>{{ lang.xfconfig['tselect_key'] }}</th>
									<th>{{ lang.xfconfig['tselect_value'] }}</th>
									<th class="text-center" width="10">{{ lang['action'] }}</th>
								</tr>
							</thead>
							<tbody id="xfSelectRows">
								{{ sOpts }}
							</tbody>
							<tfoot>
								<tr>
									<td colspan="2"></td>
									<td class="text-center" width="10">
										<button type="button" id="xfBtnAdd" title="Добавить строки" class="btn btn-primary"><i class="fa fa-plus"></i></button>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</fieldset>

		<!-- FIELD TYPE: MULTISELECT -->
		<fieldset id="type_multiselect">
			<div class="well">
				<div class="form-group">
					<label class="col-sm-5 control-label">
						{{ lang.xfconfig['tselect_default'] }}
						<span class="help-block">{{ lang.xfconfig['tselect_default_desc'] }}</span>
					</label>
					<div class="col-sm-7">
						<input type="text" name="select_default_multi" value="{{ defaults.multiselect }}" class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-5 control-label">
						{{ lang.xfconfig['tselect_storekeys'] }}
						<span class="help-block">{{ lang.xfconfig['tselect_storekeys_desc'] }}</span>
					</label>
					<div class="col-sm-7">
						<select name="select_storekeys_multi" class="form-control">{{ storekeys_opts }}</select>
					</div>
				</div>
				<div class="row">
					<label class="col-sm-5 control-label">
						{{ lang.xfconfig['tselect_options'] }}
						<span class="help-block">{{ lang.xfconfig['tselect_options_desc'] }}</span>
					</label>
					<div class="col-sm-7">
						<table id="xfSelectTable_multi" class="table-condensed table-bordered">
							<thead>
								<tr>
									<th>{{ lang.xfconfig['tselect_key'] }}</th>
									<th>{{ lang.xfconfig['tselect_value'] }}</th>
									<th class="text-center" width="10">{{ lang['action'] }}</th>
								</tr>
							</thead>
							<tbody id="xfSelectRows_multi">
								{{ m_sOpts }}
							</tbody>
							<tfoot>
								<tr>
									<td colspan="2"></td>
									<td class="text-center" width="10">
										<button type="button" id="xfBtnAdd_multi" title="Добавить строки" class="btn btn-primary"><i class="fa fa-plus"></i></button>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</fieldset>

		<!-- FIELD TYPE: CHECKBOX -->
		<fieldset id="type_checkbox">
			<div class="well">
				<div class="row">
					<label class="col-sm-5 control-label">{{ lang.xfconfig['default'] }}</label>
					<div class="col-sm-7">
						<div class="checkbox"><label><input type="checkbox" name="checkbox_default" value="1" {{ defaults.checkbox }} /></label></div>
					</div>
				</div>
			</div>
		</fieldset>

		<!-- FIELD TYPE: IMAGES -->
		<fieldset id="type_images">
			<div class="well">
				<div class="form-group">
					<label class="col-sm-5 control-label">{{ lang.xfconfig['img_maxCount'] }}</label>
					<div class="col-sm-7">
						<input type="text" size="3" name="images_maxCount" value="{{ images.maxCount }}" class="form-control" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-5 col-sm-7">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="images_imgShadow" value="1" {{ images.imgShadow }} />
								{{ lang.xfconfig['img_shadow'] }}
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-5 col-sm-7">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="images_imgStamp" value="1" {{ images.imgStamp }} />
								{{ lang.xfconfig['img_wmimage'] }}
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-5 control-label">{{ lang.xfconfig['img_preview'] }}</label>
					<div class="col-sm-7">
						<div class="input-group">
							<span class=" input-group-addon">
								<input type="checkbox" name="images_imgThumb" value="1" {{ images.imgThumb }} />
							</span>
							<input type="text" name="images_thumbWidth" value="{{ images.thumbWidth }}" class="form-control" />
							<span class="input-group-addon"> x </span>
							<input type="text" name="images_thumbHeight" value="{{ images.thumbHeight }}" class="form-control" />
							<span class="input-group-addon">px</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-5 col-sm-7">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="images_thumbShadow" value="1" {{ images.thumbShadow }}/>
								{{ lang.xfconfig['img_shadow'] }}
							</label>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-offset-5 col-sm-7">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="images_thumbStamp" value="1" {{ images.thumbStamp }}/>
								{{ lang.xfconfig['img_wmimage'] }}
							</label>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
		<!-- FIELD TYPE: /CLOSED/ -->

		<fieldset>
			<div class="form-group">
				<div class="col-sm-5">{{ lang.xfconfig['db_store'] }}</div>
				<div class="col-sm-7">
					<select name="storage" id="storage" value="{{ storage }}" onclick="storageMode(this.value);" onchange="storageMode(this.value);" class="form-control">
						<option value="0">{{ lang.xfconfig['db_store_single'] }}</option>
						<option value="1">{{ lang.xfconfig['db_store_personal'] }}</option>
					</select>
				</div>
			</div>
			<div id="storageRow" aria-expanded="false" class="collapse">
				<div class="well">
					<div class="form-group">
						<label class="col-sm-5 control-label">{{ lang.xfconfig['db_store_type'] }}</label>
						<div class="col-sm-7">
							<select name="db_type" value="{{ db_type }}" id="db.type" class="form-control">
								<option value="int">{{ lang.xfconfig['db_store_type_int'] }}</option>
								<option value="decimal">{{ lang.xfconfig['db_store_type_decimal'] }}</option>
								<option value="char">{{ lang.xfconfig['db_store_type_char'] }}</option>
								<option value="text">{{ lang.xfconfig['db_store_type_text'] }}</option>
								<option value="datetime">{{ lang.xfconfig['db_store_type_datetime'] }}</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-5 control-label">{{ lang.xfconfig['db_store_length'] }}</label>
						<div class="col-sm-7">
							<input type="text" name="db_len" value="{{ db_len }}" id="db.len" maxlength="5" class="form-control" />
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-5">{{ lang.xfconfig['required'] }} <span class="help-block">{{ lang.xfconfig['required_desc'] }}</span></div>
				<div class="col-sm-7">
					<select name="required" class="form-control">{{ required_opts }}</select>
				</div>
			</div>
			{% if (sectionID != 'tdata') %}
			<div class="form-group">
				<div class="col-sm-5">{{ lang.xfconfig['location'] }}<span class="help-block">{{ lang.xfconfig['location_desc'] }}</span></div>
				<div class="col-sm-7">
					<input type="text" name="area" value="{{ area }}" class="form-control" />
				</div>
			</div>
			{% endif %}
			<div class="form-group">
				<div class="col-sm-offset-5 col-sm-7">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="disabled" value="1"{% if (flags.disabled) %}checked="checked"{% endif %} />
							{{ lang.xfconfig['disabled'] }}
						</label>
					</div>
				</div>
			</div>
			{% if (sectionID == 'users') and (type != 'images') %}
			<div class="form-group">
				<div class="col-sm-offset-5 col-sm-7">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="regpage" value="1"{% if (flags.regpage) %}checked="checked"{% endif %} />
							{{ lang.xfconfig['regpage'] }}
						</label>
					</div>
				</div>
			</div>
			{% endif %}
		</fieldset>

		<fieldset>
			<div class="well">
				<div class="row">
					<div class="col-sm-offset-5 col-sm-7">
						<input type="submit" id="xfBtnSubmit" value="{% if (flags.editMode) %}{{ lang.xfconfig['edit'] }}{% else %}{{lang.xfconfig['save'] }}{% endif %}" class="btn btn-success" />
					</div>
				</div>
			</div>
		</fieldset>
	</form>
</div>

<script>
function clx(mode) {
 document.getElementById('type_text').style.display		= (mode == 'text')?		'block':'none';
 document.getElementById('type_textarea').style.display = (mode == 'textarea')?	'block':'none';
 document.getElementById('type_select').style.display	= (mode == 'select')?	'block':'none';
 document.getElementById('type_multiselect').style.display	= (mode == 'multiselect')?	'block':'none';
 document.getElementById('type_checkbox').style.display	= (mode == 'checkbox')?	'block':'none';
 document.getElementById('type_images').style.display	= (mode == 'images')?	'block':'none';
}
function storageMode(mode) {
// alert(document.getElementById('storageRow'));
 if (mode == 0) {
 $('#storageRow').collapse('hide');
 document.getElementById('db.type').disabled = true;
 document.getElementById('db.len').disabled = true;
 } else {
 $('#storageRow').collapse('show');
 document.getElementById('db.type').disabled = false;
 document.getElementById('db.len').disabled = false;
 }
 
}

</script>
<script type="text/javascript">
clx('{{ type }}');
document.getElementById('storage').value = '{{ storage }}';
document.getElementById('db.type').value = '{{ db_type }}';
storageMode(document.getElementById('storage').value);

var soMaxNum = $('#xfSelectTable >tbody >tr').length+1;

$('#xfSelectTable a').click(function(){
	if ($('#xfSelectTable >tbody >tr').length > 1) {
		$(this).parent().parent().remove();
	} else {
		$(this).parent().parent().find("input").val('');
	}
});

$("#xfBtnSubmit").click(function() {
	// Check if type == 'select'
	if ($("#xfBtnType").val() == 'select') {
		// Prepare list of data



	}

});

// jQuery - INIT `select` configuration
$("#xfBtnAdd").click(function() {
	var xl = $('#xfSelectTable tbody>tr:last').clone();
	xl.find("input").val('');
	xl.find("input").eq(0).attr("name", "so_data["+soMaxNum+"][0]");
	xl.find("input").eq(1).attr("name", "so_data["+soMaxNum+"][1]");
	soMaxNum++;

	xl.insertAfter('#xfSelectTable tbody>tr:last');
	$('#xfSelectTable a').click(function(){
		if ($('#xfSelectTable >tbody >tr').length > 1) {
			$(this).parent().parent().remove();
		} else {
			$(this).parent().parent().find("input").val('');
		}
	});
});


var soMaxNum_multi = $('#xfSelectTable_multi >tbody >tr').length+1;

$('#xfSelectTable_multi a').click(function(){
	if ($('#xfSelectTable_multi >tbody >tr').length > 1) {
		$(this).parent().parent().remove();
	} else {
		$(this).parent().parent().find("input").val('');
	}
});

$("#xfBtnAdd_multi").click(function() {
	var xl = $('#xfSelectTable_multi tbody>tr:last').clone();
	xl.find("input").val('');
	xl.find("input").eq(0).attr("name", "mso_data["+soMaxNum_multi+"][0]");
	xl.find("input").eq(1).attr("name", "mso_data["+soMaxNum_multi+"][1]");
	soMaxNum_multi++;

	xl.insertAfter('#xfSelectTable_multi tbody>tr:last');
	$('#xfSelectTable_multi a').click(function(){
		if ($('#xfSelectTable_multi >tbody >tr').length > 1) {
			$(this).parent().parent().remove();
		} else {
			$(this).parent().parent().find("input").val('');
		}
	});
});



</script>