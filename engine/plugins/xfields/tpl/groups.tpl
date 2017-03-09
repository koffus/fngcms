<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{l_home}</a></li>
	<li><a href="admin.php?mod=extras" title="{l_extras}">{l_extras}</a></li>
	<li class="active">xfields</li>
</ul>

<!-- Info content -->
<div class="page-main">

	<form class="form-horizontal">

		{% include 'plugins/xfields/tpl/navi.tpl' %}

		<table class="table table-condensed">
			<thead>
				<tr>
					<th colspan="2">Группы</th>
					<th colspan="2">Поля, находящиеся в группе (<span id="grpName">n/a</span>)</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<td width="200" colspan="2"><select name="gList" id="gList" onclick="selectGroupList(0);" onkeyup="selectGroupList(0);" class="form-control"size="15"></select></td>
				<td colspan="3">
				<div class="fldHead">
				<table width="100%" class="fldList" id="fList">
				<tr>
				 <td width="50">date</td>
				 <td>Дата добавления новости</td>
				 <td width="90" align="right" nowrap>(up) (down) (del)</td>
				</tr>
				<tr>
				 <td width="50">&nbsp;date</td>
				 <td>Дата добавления новости</td>
				 <td width="90" align="right" nowrap>(up) (down) (del)</td>
				</tr>
				</table>
				</div>
				</td>
				</tr>
				<tr class="contRow1">
				<td width="70" nowrap="nowrap">ID группы:</td>
				<td><input id="edGrpId" style="width: 200px; height: 15px;"> <input type="button" id="btnDelGroup" class="button" value="Удалить"/></td>
				<td width="90">Добавить поле:</td><td><select style="width: 200px; height: 19px; border: #BFBFBF 1px solid;" id="selectFList"></select> <input type="button" id="btnAddField" class="button" value="Добавить"/></td>
				</tr>
				<tr class="contRow1">
				<td width="70" nowrap="nowrap">Имя группы:</td>
				<td><input id="edGrpName" style="width: 200px; height: 15px;"></td>
				</tr>
			</tbody>
		</table>

		<div class="well text-center">
			<input type="button" id="btnModGroup" class="btn btn-success" value="Добавить" />
		</div>
	</form>

</div>

<script type="text/javascript" src="{{ scriptLibrary }}/ajax.js"></script>
<script type="text/javascript" src="{{ scriptLibrary }}/admin.js"></script>

<script type="text/javascript" language="javascript">
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
			 //r.onclick = function() { alert(this.tag); }
			 var tl = document.createElement('td');
			 tl.innerHTML = fldName;
			 tl.width = 50;
			 r.appendChild(tl);

			 tl = document.createElement('td');
			 tl.innerHTML = fConfig[fldName]?fConfig[fldName]['title']:'n/a';
			 r.appendChild(tl);

			 tl = document.createElement('td');
			 tl.width = 70;
			 tl.style.align = 'right';
			 tl.innerHTML =	'<img src="/engine/skins/default/images/up.gif" onclick="fieldModifyRequest(this.parentNode.parentNode.tag, 1);"/> '+
			 				'<img src="/engine/skins/default/images/down.gif" onclick="fieldModifyRequest(this.parentNode.parentNode.tag, 2);"/> '+
			 				'<img src="/engine/skins/default/images/delete.gif" onclick="fieldModifyRequest(this.parentNode.parentNode.tag, 3);"/>';
			 r.appendChild(tl);
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
		} else {
			document.getElementById('edGrpId').readOnly = true;
			document.getElementById('edGrpId').style.backgroundColor= '#EAF0F7';
			document.getElementById('btnModGroup').value = "Сохранить";
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
	//	alert('ADD');
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
 //var dOut = json_encode(dData);

 var linkTX = new sack();
 linkTX.requestFile = 'rpc.php';
 linkTX.setVar('json', '1');
 linkTX.setVar('methodName', method);
 linkTX.setVar('params', json_encode(params));
 linkTX.method='POST';
 linkTX.onComplete = function() {
	if (linkTX.responseStatus[0] == 200) {
		var resTX;
 try {
 	 		resTX = eval('('+linkTX.response+')');
 		} catch (err) { alert('{l_fmsg.save.json_parse_error} '+linkTX.response); }

 		// First - check error state
 		if (!resTX['status']) {
 			// ERROR. Display it
 			alert('Error ('+resTX['errorCode']+'): '+resTX['errorText']);
 		} else {
 			//alert('Request complete, answer: '+resTX['data']+'; '+typeof(resTX['config']));
 			if (typeof(resTX['config'])=='object') {
 				gConfig = resTX['config'];
 				drawGroupList(gListValue);
 				selectGroupList(1);
			}
 		}
 	} else {
 		alert('{l_fmsg.save.httperror} '+linkTX.responseStatus[0]);
	}
 }
 linkTX.onShow();
 linkTX.runAJAX();
}


initEvents();
drawGroupList(0);
grpList.selectedIndex = 0;
selectGroupList(0);
generateFieldList();
//rpcRequest('plugin.xfields.demo', { 'action' : 'add', 'name' : 'infomania'});
</script>