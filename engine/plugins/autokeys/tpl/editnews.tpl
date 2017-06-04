<div class="form-group">
	<div class="col-sm-9 col-sm-offset-3">
		<div class="input-group">
			<span class="input-group-addon">
				<input type="checkbox" id="autokeys_generate" name="autokeys_generate" value="1" {% if (flags.checked) %} checked="checked" {% endif %}class="check" />
			</span>
			<input type="text" id="autokeysArea" class="form-control" placeholder="Автоматическая генерация ключевых слов" readonly />
			<span class="input-group-btn">
				<button type="button" id="autokeysArea" class="btn btn-default " onclick="autokeysAjaxUpdate();" title="Генерировать keywords и теги"><i class="fa fa-refresh"></i></button>
			</span>
		</div>
	</div>
</div>

<script>
var autokeysAjaxUpdate = function() {
	$.ajax({
		type: 'POST',
		url: '{{ admin_url }}/rpc.php',
		dataType: 'text',
		data: {
			json: 1,
			rndval: new Date().getTime(),
			methodName : 'plugin.autokeys.generate',
			params: json_encode({
					'token': '{{ token }}', 
					'title' : $('#newsTitle').val(),
					'content' : $('#ng_news_content').val(),
				}),
		},
		beforeSend: function() {ngShowLoading();},
		error: function() {ngHideLoading();$.notify({message: '{{ lang['rpc_httpError'] }}'},{type: 'danger'});},
	}).done(function( data ) {
		ngHideLoading();
		try {var resTX = eval('('+data+')');} catch (err) {$.notify({message:'{{ lang['rpc_jsonError'] }} '+data},{type: 'danger'});}
		if (!resTX['status']) {
			$.notify({message:'Error ['+resTX['errorCode']+']: '+resTX['errorText']},{type: 'danger'});
		} else {
			$("#newsKeywords").val(resTX['data']);
		}
	});
};
</script>