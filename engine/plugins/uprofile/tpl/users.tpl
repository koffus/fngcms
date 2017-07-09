<!--div id="uprofileReplaceForm"-->
	<h2 class="section-title">{{ lang.uprofile['profile_of'] }} <b>{{ user.name }} </b><!--[id: {{ user.id }}]--></h2>

	<div class="row">
		<div class="col-md-3 text-center">
			<a href="{{ user.avatar }}" target="_blank"><img src="{{ user.avatar }}" alt="{{ user.name }}" class="rounded-circle" /></a>
			{% if (user.flags.hasPhoto) %}<a href="{{ user.photo }}" target="_blank"><img src="{{ user.photo_thumb }}" alt="{{ user.name }}" class="rounded-circle" /></a>{% endif %}
			{% if not (global.user.status == 0) and not (user.flags.isOwnProfile) and pluginIsActive('pm') %}
				<p><a href="{{ home }}/plugin/pm/?action=write&name={{ user.name }}" class="btn btn-primary"><i class="fa fa-envelope"></i> {{ lang.uprofile['write_pm'] }}</a></p>
			{% endif %}
		</div>

		<div class="col-md-9">
			<table class="table table-user-information">
				<tbody>
					<tr>
						<td>{{ lang.uprofile['status'] }}</td><td>{{ user.status }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['regdate'] }}</td><td>{{ user.reg }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['last'] }}</td><td>{{ user.last }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['all_news'] }}</td><td>{{ user.news }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['all_comments'] }}</td><td>{{ user.com }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['site'] }}</td><td>{{ user.site }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['icq'] }}</td><td>{{ user.icq }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['from'] }}</td><td>{{ user.from }}</td>
					</tr>
					<tr>
						<td colspan="2">{{ lang.uprofile['about'] }}<br />{{ user.info }}</td>
					</tr>
					{% if (pluginIsActive('xfields')) %}
						{% for field in p.xfields %}
							{% if (field.type == 'images') %}
								{{ field.value }}
							{% else %}
							<tr>
								<td>{{ field.title }}</td>
								<td>
									{% if (field.type == 'checkbox') %}
										{% if (field.value) %}{{ lang['yesa'] }}{% else %}{{ lang['noa'] }}{% endif %}
									{% else %}
										{{ field.value }}
									{% endif %}
								</td>
							</tr>
							{% endif %}
						{% endfor %}
					{% endif %}
					{% if (user.flags.isOwnProfile) %}
					<tr>
						<td colspan="2"><br /><a href="{{ home }}/profile.html" class="btn btn-primary" onclick="/*ng_uprofile_editCall(); return false;*/">{{ lang.uprofile['edit_profile'] }}</a></td>
					</tr>
					{% endif %}
				</tbody>
			</table>
			{% if pluginIsActive('xfields') %}
				{{ plugin_xfields_0 }}
				{{ plugin_xfields_1 }}
			{% endif %}
		</div>
	</div>
</div>

{% if (user.flags.isOwnProfile) %}
<script>
function ng_uprofile_editCall() {
	$.ajax({
		type: 'POST',
		url: '{{ admin_url }}/rpc.php',
		dataType: 'json',
		data: {
			json: 1,
			rndval: new Date().getTime(),
			methodName : 'plugin.uprofile.editForm',
			params: json_encode({
					'token': '{{ token }}',
				}),
		},
		beforeSend: function() {ngShowLoading();},
		error: function() {ngHideLoading();alert('HTTP error during request');},
	}).done(function( data ) {
		ngHideLoading();
		try {resTX = eval(data);} catch (err) {alert('Error parsing JSON output. Result: ' + data);}
		if (!resTX['status'])
			alert('Error [' + resTX['errorCode'] + ']: ' + resTX['errorText']);
		else
			$('#uprofileReplaceForm').html(resTX['data']);
	});

}
</script>
{% endif %}-->