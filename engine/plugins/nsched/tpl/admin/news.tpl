{% if flags.permPublish %}
<div class="form-group">
	<label class="col-sm-3 control-label">{{ lang['nsched:activate'] }}</label>
	<div class="col-sm-9">
		<input id="nsched_activate" name="nsched_activate" value="{{ nactivate }}" class="form-control" />
		<span class="help-block">{{ lang['nsched:activate#desc'] }}</span>
	</div>
</div>
{% endif %}
{% if flags.permUnPublish %}
<div class="form-group">
	<label class="col-sm-3 control-label">{{ lang['nsched:deactivate'] }}</label>
	<div class="col-sm-9">
		<input id="nsched_deactivate" name="nsched_deactivate" value="{{ ndeactivate }}" class="form-control" />
		<span class="help-block">{{ lang['nsched:deactivate#desc'] }}</span>
	</div>
</div>
{% endif %}
<script type="text/javascript">
$(function() {
	$('#nsched_activate').datetimepicker({format:'YYYY-MM-DD HH:mm',locale: "{{ lang['langcode'] }}", currentText: '{{ nactivate }}'});
	$('#nsched_deactivate').datetimepicker({format:'YYYY-MM-DD HH:mm',locale: "{{ lang['langcode'] }}", currentText: '{{ ndeactivate }}'});
});
</script>