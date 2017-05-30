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

	$.ajax({
		type: 'POST',
		url: '{{ admin_url }}/rpc.php',
		dataType: 'json',
		data: {
			json: 1,
			rndval: new Date().getTime(),
			methodName : 'admin.extras.getPluginConfig',
			params: json_encode({
					'token': '{{ token }}',
				}),
		},
		beforeSend: function() {ngShowLoading();},
		error: function() {ngHideLoading();$.notify({message: '{{ lang['rpc_httpError'] }}'},{type: 'danger'});},
	}).done(function( data ) {
		ngHideLoading();
		try {resTX = eval(data);} catch (err) {$.notify({message:'{{ lang['rpc_jsonError'] }} '+data},{type: 'danger'});}
		if (!resTX['status']) {
			$.notify({message:'Error ['+resTX['errorCode']+']: '+resTX['errorText']},{type: 'danger'});
		} else {
			var line = resTX['content'];
			var newline = line.replace(/\\u/g, "%u");
			$('#configArea').val(unescape(newline));
		}
	});

}

function showContent() {
 alert($('#configArea').val());
}

</script>