<script>
$(function() {
	$('#newsKeywords').replaceWith('\
			<div class="input-group">\
				<input type="text" name="keywords" id="newsKeywords" value="'+$('#newsKeywords').val()+'" tabindex="7" class="form-control" maxlength="255" />\
				<span class="input-group-btn">\
					<button type="button" id="autokeysArea" class="btn btn-default " onclick="autokeysAjaxUpdate();" title="Генерировать ключевые слова"><i class="fa fa-refresh"></i></button>\
				</span>\
			</div>\
			<label class="control-label help-block"><input type="checkbox" id="autokeys_generate" name="autokeys_generate" value="1" {% if (flags.checked) %} checked="checked" {% endif %}class="check" /> Автоматическая генерация ключевых слов при сохранении новости</label>');
});

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