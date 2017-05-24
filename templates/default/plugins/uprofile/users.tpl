<h2 class="section-title">{{ lang.uprofile['profile_of'] }} {% if (user.flags.isOwnProfile) %}<a href="{{ home }}/profile.html">{% endif %}<b>{{ user.name }} </b>{% if (user.flags.isOwnProfile) %}</a>{% endif %}<!--[id: {{ user.id }}]--></h2>

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
				{% if (user.flags.isOwnProfile) %}
				<tr>
					<td colspan="2"><br /><a href="{{ home }}/profile.html" class="btn btn-primary">{{ lang.uprofile['edit_profile'] }}</a></td>
				</tr>
				{% endif %}
			</tbody>
		</table>
	</div>
</div>