<h2 class="section-title">{{ lang.uprofile['profile_of'] }} <b>{{ user.name }}</b></h2>

<form id="profileForm" action="{{ form_action }}" method="post" enctype="multipart/form-data">
	<input type="hidden" name="token" value="{{ token }}" />

	<div class="row">
		<div class="col-md-3 text-center">
			{% if (flags.avatarAllowed) %}
				<a href="{{ user.avatar }}" target="_blank"><img src="{{ user.avatar }}" alt="{{ user.name }}" class="rounded-circle" /></a>
				{% if (user.flags.hasAvatar) %}
					<p class="checkbox"><label><input type="checkbox" name="delavatar" id="delavatar" /> {{ lang.uprofile['avatar_delete'] }}</label></p>
				{% endif %}
			{% endif %}

			{% if (flags.photoAllowed and user.flags.hasPhoto) %}
				<a href="{{ user.photo }}" target="_blank"><img src="{{ user.photo_thumb }}" alt="{{ user.name }}" class="rounded-circle" /></a>
				<p class="checkbox"><label><input type="checkbox" name="delphoto" id="delphoto" /> {{ lang.uprofile['photo_delete'] }}</label></p>
			{% endif %}
		</div>

		<div class="col-md-9">
			<table class="table table-user-information">
				<tbody>
					<tr>
						<td>{{ lang.uprofile['status'] }}</td><td>{{ user.status }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['regdate'] }}</td><td>{{ user.regdate }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['last'] }}</td><td>{{ user.last }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['all_news'] }}</td><td>{{ user.news }}</td>
					</tr>
					<tr>
						<td>{{ lang.uprofile['all_comments'] }}</td><td>{{ user.comments }}</td>
					</tr>
					<tr>
						<td><label class="control-label">{{ lang.uprofile['site'] }}</label></td><td><input type="text" name="editsite" value="{{ user.site }}" class="form-control input-sm" /></td>
					</tr>
					<tr>
						<td><label class="control-label">{{ lang.uprofile['icq'] }}</label></td><td><input type="text" name="editicq" value="{{ user.icq }}" class="form-control input-sm" /></td>
					</tr>
					<tr>
						<td><label class="control-label">{{ lang.uprofile['from'] }}</label></td><td><input type="text" name="editfrom" value="{{ user.from }}" class="form-control" /></td>
					</tr>
					<tr>
						<td><label class="control-label">{{ lang.uprofile['about'] }}</label></td><td><textarea name="editabout" id="editabout" rows="5" class="form-control" {% if (info_sizelimit > 0) %} maxlength="{about_sizelimit}" {% endif %}>{{ user.info }}</textarea>
						{% if (info_sizelimit > 0) %}<span class="help-block">{{ lang.uprofile['sizelimit_min'] }} {{ lang.uprofile['characters'] }}: <b id="sizelimit_text">{about_sizelimit}</b></span>{% endif %}</td>
					</tr>
					{% if (flags.avatarAllowed) %}
						<tr>
							<td><label class="control-label">{{ lang.uprofile['avatar_upload'] }}</label></td><td><input type="file" name="newavatar" /></td>
						</tr>
					{% else %}
						<tr>
							<td><label class="control-label">{{ lang.uprofile['avatar'] }}</label></td><td>{{ lang.uprofile['avatars_denied'] }}</td>
						</tr>
					{% endif %}
					{% if (flags.photoAllowed) %}
						<tr>
							<td><label class="control-label">{{ lang.uprofile['photo_upload'] }}</label></td><td><input type="file" name="newphoto" /></td>
						</tr>
					{% else %}
						<tr>
							<td><label class="control-label">{{ lang.uprofile['photo'] }}</label></td><td>{{ lang.uprofile['photos_denied'] }}</td>
						</tr>
					{% endif %}
					<tr>
						<td><label class="control-label">{{ lang.uprofile['email'] }}</label></td><td><input type="text" name="editmail" value="{{ user.email }}" class="form-control input-sm" /></td>
					</tr>
					<tr>
						<td><label class="control-label">{{ lang.uprofile['new_pass'] }}</label></td>
						<td><input type="password" name="editpassword" class="form-control input-sm" onclick="$('#oldpass').css('display', '');" onchange="$('#oldpass').css('display', '');" /></td>
					</tr>
					<tr id="oldpass" style="display:none;">
						<td><label class="control-label">{{ lang.uprofile['oldpass'] }}</label></td>
						<td><input type="password" name="oldpass" value="" class="form-control input-sm" />
						<span class="help-block">{{ lang.uprofile['oldpass#desc'] }}</span</td>
					</tr>
					{% if (pluginIsActive('xfields')) %}
						{% if (p.xfields.fields) %}
							<tr><td colspan="2"><legend>{{ lang['xfields:group_title'] }}</legend></td></tr>
						{% endif %}
						{% for field in p.xfields.fields %}
							{% if (field.data['type'] == 'images') %}
								<tr><td colspan="2"><legend>{{field.title}}{% if field.flags.required %} <b>*</b>{% endif %}</legend>{{field.input}}</td></tr>
							{% else %}
							<tr>
								{% if (field.data['type'] == 'checkbox') %}
									<td></td>
									<td>{{field.input}} {{field.title}}{% if field.flags.required %} <b>*</b>{% endif %}</td>
								{% else %}
									<td>{{field.title}}{% if field.flags.required %} <b>*</b>{% endif %}</td>
									<td>{{field.input}}</td>
								{% endif %}
							</tr>
							{% endif %}
						{% endfor %}
					{% endif %}
				</tbody>
			</table>
			<input type="submit" onclick="return validate_form();" value="{{ lang.uprofile['save'] }}" class="btn btn-success" />
		</div>
	</div>
</form>

<script>
	$(function() {
		var sizelimit = {{ info_sizelimit }};
		if(sizelimit > 0 ) {
			calculateMaxLen($('#editabout'), $('#sizelimit_text'), sizelimit);
		}

		$('#editabout').on('click focus keyup', function(event) {
			if(sizelimit > 0 ) {
				calculateMaxLen($(this), $('#sizelimit_text'), sizelimit);
			}
		});
	});

	function validate_form() {
		var f = document.getElementById('profileForm');
		// ICQ
		var icq = f.editicq.value;
		if ((icq.length > 0)&&(! icq.match(/^\d{4,10}$/))) { 
			alert("{{ lang.uprofile['wrong_icq'] }}"); 
			return false; 
		}
		// Email
		var email = f.editmail.value;
		if ((email.length > 0) && (! emailCheck(email))) {
			alert("{{ lang.uprofile['wrong_email'] }}");
			return false;
		}
		// About
		var about = f.editabout.value;
		if (({{ info_sizelimit }} > 0) && (about.length > {{ info_sizelimit }})) {
			alert("{{ info_sizelimit_text }}");
			return false;	
		}
		return true;
	}
</script>
