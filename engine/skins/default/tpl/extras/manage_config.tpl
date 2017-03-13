<!-- Navigation bar -->
<div class="navigation">
	<div class="row">
		<div class="col col-md-12">
			<ul class="breadcrumb">
				<li><a href="admin.php">{{ lang['home'] }}</a></li>
				<a href="admin.php?mod=configuration">{{ lang['extras'] }}</a>
				<li class="active">{{ lang['manage_vars'] }}</li>
			</ul>
		</div>
	</div>
</div>

<form method="post" action="admin.php?mod=extras&manageConfig=1">
<input type="hidden" name="token" value="{{ token }}"/>
<input type="hidden" name="mod" value="extras"/>
<input type="hidden" name="manageConfig" value="1"/>
<input type="hidden" name="action" value="commit"/>

<div id="configAreaX"></div>
<textarea name="config" id="configArea" cols="120" rows="40" style="width: 99%; font: normal 11px/14px Courier,Tahoma,sans-serif;"></textarea>
<!-- <input type="submit" value="Commit changes"/> --> &nbsp; <input type="button" value="Load data" onclick="loadData(); return false;"/> &nbsp; <input type="button" value="Show content" onclick="showContent(); return false;"/>
</form>

<script>
function loadData() {
	ngShowLoading();
	$.post('{{ admin_url }}/rpc.php', { json : 1, methodName : 'admin.extras.getPluginConfig', rndval: new Date().getTime(), params : json_encode({ token : '{{ token }}' }) }, function(data) {
		ngHideLoading();
		// Try to decode incoming data
		try {
			resTX = eval('('+data+')');
		} catch (err) { alert('Error parsing JSON output. Result: '+linkTX.response); }
		if (!resTX['status']) {
			$.notify({message: 'Error ['+resTX['errorCode']+']: '+resTX['errorText']},{type: 'danger'});
		} else {
			var line = resTX['content'];
			var newline = line.replace(/\\u/g, "%u");
			$('#configArea').val(unescape(newline));
		}
	}, "text").error(function() { ngHideLoading(); $.notify({message: 'HTTP error during request'},{type: 'danger'}); });
}

function showContent() {
 alert($('#configArea').val());
}


</script>