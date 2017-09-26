<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
	<li class="active">xfields</li>
</ul>

<!-- Info content -->
<div class="page-main">

	<form class="form-horizontal">

		{% include 'plugins/xfields/tpl/admin/navi.tpl' %}

		<br>
		<div class="row">
			<div class="col-md-6">
				<div class="well">
					<fieldset class="">
						<legend>Группы</legend>
								<div class="form-group">
							<div class="col-md-12">
									<select name="gList" id="gList" onclick="selectGroupList(0);" onkeyup="selectGroupList(0);" size="6" class="form-control"></select>
								</div>
								</div>
							
								<div class="form-group">
								<label for="" class="col-md-4 control-label">ID группы:</label>
							<div class="col-md-8">
								<input type="text" id="edGrpId" class="form-control">
								</div>
								</div>
								
								
								<div class="form-group">
									<label for="" class="col-md-4 control-label">Имя группы:</label>
							<div class="col-md-8">
									<input id="edGrpName" class="form-control">
								</div>
								</div>
								<div class="row">
							<div class="col-md-8 col-md-offset-4">
									<input type="button" id="btnModGroup" class="btn btn-success" value="Добавить" />
									<input type="button" id="btnDelGroup" class="btn btn-danger" value="Удалить"/>
								</div>
								</div>
					</fieldset>
				</div>
			</div>
			<div class="col-md-6">
				<div class="well" id="fldGroup">
					<fieldset>
						<legend>Поля, находящиеся в группе (<span id="grpName">n/a</span>)</legend>
						<table class="table table-condensed table-bordered">
							<thead>
								<tr>
									<th>ID поля</th>
									<th>Название поля</th>
									<th class="text-right">{{ lang['action'] }}</th>
								</tr>
							</thead>
							<tbody id="fList">
								<tr>
									<td>date</td>
									<td>Дата добавления новости</td>
									<td nowrap class="text-right">(up) (down) (del)</td>
								</tr>
								<tr>
									<td>date</td>
									<td>Дата добавления новости</td>
									<td nowrap class="text-right">(up) (down) (del)</td>
								</tr>
							</tbody>
						</table>
						<div class="form-group">
							<label for="" class="col-md-4 control-label">Добавить поле:</label>
							<div class="col-md-8">
								<div class="input-group">
									<select id="selectFList" class="form-control"></select>
									<span class="input-group-btn">
										<input type="button" id="btnAddField" class="btn btn-success" value="Добавить"/>
									</span>
								</div>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
<!--
// Connect to configuration data
var gConfig	= {{ json['groups.config'] }};
var fConfig	= {{ json['fields.config'] }};

var grpList	= document.getElementById('gList');
var fldList	= document.getElementById('fList');

var gListValue	= 0;
var gFldValue	= 0;

function selectGroupList(force) {
	if ((force == 1) || (grpList.value != gListValue)) {
		document.getElementById('grpName').innerHTML = (grpList.value != '')?grpList.value:'n/a';
		while (fldList.rows.length)
			fldList.deleteRow(0);

		grpList.parentNode.enabled = false;
		if (grpList.value != '') {
			var rowNo = 0;
			for (var i in gConfig[grpList.value]['entries']) {
				var fldName = gConfig[grpList.value]['entries'][i];

				var r = fldList.insertRow(-1);
				r.tag = rowNo++;
				var tl = document.createElement('td');
				tl.innerHTML = fldName;
				r.appendChild(tl);

				tl = document.createElement('td');
				tl.innerHTML = fConfig[fldName]?fConfig[fldName]['title']:'n/a';
				r.appendChild(tl);

				tl = document.createElement('td');
				tl.setAttribute('class', 'text-right');
				tl.setAttribute('nowrap', 'nowrap');
				tl.innerHTML =	'<div class="btn-group"><a href="#" class="btn btn-default" onclick="fieldModifyRequest(this.parentNode.parentNode.parentNode.tag, 1);"><i class="fa fa-arrow-up"></i></a>' + 
								'<a href="#" class="btn btn-default" onclick="fieldModifyRequest(this.parentNode.parentNode.parentNode.tag, 2);"><i class="fa fa-arrow-down"></i></a>' + 
								'<a href="#" class="btn btn-danger" onclick="fieldModifyRequest(this.parentNode.parentNode.parentNode.tag, 3);"><i class="fa fa-trash-o"></i></a></div>';
				r.appendChild(tl);
			}
			if ( rowNo == 0 ) {
				var r = fldList.insertRow(-1);
				var xCell = r.insertCell(0);
				xCell.setAttribute('colspan', '3');
				xCell.innerHTML = 'В этой группе нет полей';
			}
		}
		grpList.parentNode.enabled = true;
		gListValue = grpList.value;
		document.getElementById('edGrpId').value = gListValue;
		document.getElementById('edGrpName').value = (gListValue != '')?gConfig[gListValue]['title']:'';
		if (gListValue == '') {
			document.getElementById('edGrpId').readOnly = false;
			document.getElementById('edGrpId').style.backgroundColor= '#FFFFFF';
			document.getElementById('btnModGroup').value = "Добавить";
			document.getElementById('btnDelGroup').style.display = 'none';
			document.getElementById('fldGroup').style.display = 'none';
		} else {
			document.getElementById('edGrpId').readOnly = true;
			document.getElementById('edGrpId').style.backgroundColor= '#EAF0F7';
			document.getElementById('btnModGroup').value = "Сохранить";
			document.getElementById('btnDelGroup').style.display = '';
			document.getElementById('fldGroup').style.display = '';
		}
	}
}

function fieldModifyRequest(id, action) {
	// Check if we're in EDIT mode
	if (!document.getElementById('edGrpId').readOnly) {
		alert('Group is not selected');
		return;
	}

	var fn = gConfig[gListValue]['entries'][id];
	var fa = 'fld'+((action==1)?'Up':((action==2)?'Down':'Del'));
	//alert('FieldName ('+id+')['+gListValue+']: '+fn+ '; action: '+fa);
	//return;
	rpcRequest(
		'plugin.xfields.group.modify',
		{
			'action' : fa,
			'utoken' : 'UTOKEN',
			'id'	 : document.getElementById('edGrpId').value,
			'field'	 : fn,
		});
}

function drawGroupList(gID) {
	grpList.options.length = 0;

	for (var i in gConfig) {
		var o = document.createElement('option');
		o.value=i;
		o.text = i + ' :: '+gConfig[i]['title'];
		grpList.options[grpList.options.length] = o;
	}
	var o = document.createElement('option');
	o.value = '';
	o.text = '** новая группа **';
	grpList.options[grpList.options.length] = o;
	grpList.value = gID;
}

function generateFieldList() {
	var items = document.getElementById('selectFList');
	items.options.length = 0;
	for (var i in fConfig) {
		var o = document.createElement('option');
		o.value = i;
		o.text = i + ' :: ' + fConfig[i]['title'];
		items.options.add(o);
	}
}

function initEvents() {
	document.getElementById('btnAddField').onclick = function() {
		var value = document.getElementById('selectFList').value;
		if (gListValue != '') {
			// Check if field is already in list
			var dup = 0;
			for (var i in gConfig[gListValue]['entries']) {
				if (gConfig[gListValue]['entries'][i] == value)
					dup = 1;
			}

			if (dup) {
				alert('Duplicate entry');
			} else {
				gConfig[gListValue]['entries'][gConfig[gListValue]['entries'].length] = value;
				selectGroupList(1);
			}
		}
	}
	document.getElementById('btnModGroup').onclick = function() {
		rpcRequest(
			'plugin.xfields.group.modify',
			{
				'action' : 'grp'+(document.getElementById('edGrpId').readOnly?'Edit':'Add'),
				'utoken' : 'UTOKEN',
				'id'	 : document.getElementById('edGrpId').value,
				'name'	 : document.getElementById('edGrpName').value
			});
	}
	document.getElementById('btnDelGroup').onclick = function() {
		// Check if we're in EDIT mode
		if (!document.getElementById('edGrpId').readOnly) {
			alert('Nothing to delete!');
			return;
		}
		rpcRequest(
			'plugin.xfields.group.modify',
			{
				'action' : 'grpDel',
				'utoken' : 'UTOKEN',
				'id'	 : document.getElementById('edGrpId').value
			});
	}

	document.getElementById('btnAddField').onclick = function() {
		// Check if we're in EDIT mode
		if (!document.getElementById('edGrpId').readOnly) {
			alert('Group is not selected');
			return;
		}
		rpcRequest(
			'plugin.xfields.group.modify',
			{
				'action' : 'fldAdd',
				'utoken' : 'UTOKEN',
				'id'	 : document.getElementById('edGrpId').value,
				'field'	 : document.getElementById('selectFList').value,
			});
	}

}

function rpcRequest(method, params) {
    $.reqJSON('{{ admin_url }}/rpc.php', method, params, function(json) {
        if ('object' == typeof(json.config)) {
            gConfig = json.config;
            drawGroupList(gListValue);
            selectGroupList(1);
            $.notify({message: json.msg},{type: 'success'});
        }
    });
}

initEvents();
drawGroupList(0);
grpList.selectedIndex = 0;
selectGroupList(0);
generateFieldList();
</script>