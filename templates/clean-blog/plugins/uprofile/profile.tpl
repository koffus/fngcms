<script type="text/javascript">
	document.ready=function()
 {
 var sizelimit = {{ info_sizelimit }};
 if(sizelimit <= 0 ) { document.getElementById('sizelimit_text').style.display = "none"; }
 }
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

<!-- Page Header -->
<header class="intro-header" style="background-image: url('{{ tpl_url }}/img/home-bg.jpg')">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<div class="post-heading">
					<h1>{{ user.name }}</h1>
					<hr class="small">
					<span class="subheading">{{ lang.uprofile['header.edit'] }}</span>
				</div>
			</div>
		</div>
	</div>
</header>
<!-- Main Content -->
<div class="container">
	<div class="row">
		<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
			<form id="profileForm" method="post" action="{{ form_action }}" enctype="multipart/form-data">
				<input type="hidden" name="token" value="{{ token }}"/>

				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['email'] }}</label>
					<div class="col-sm-8">
						<input type="text" name="editmail" value="{{ user.email }}" class="form-control" />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['site'] }}:</label>
					<div class="col-sm-8">
						<input type="text" name="editsite" value="{{ user.site }}" class="form-control" />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['icq'] }}:</label>
					<div class="col-sm-8">
						<input type="text" name="editicq" value="{{ user.icq }}" class="form-control" />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['from'] }}:</label>
					<div class="col-sm-8">
						<input type="text" name="editfrom" value="{{ user.from }}" class="form-control" />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['about'] }}: </label>
					<div class="col-sm-8">
						<textarea style="height: 60px;" name="editabout" class="form-control">{{ user.info }}</textarea>
					{% if (info_sizelimit > 0) %}<div class="label-desc" id="sizelimit_text">{{ lang.uprofile['sizelimit_min'] }} {about_sizelimit} {{ lang.uprofile['characters'] }}</div>{% endif %}
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['new_pass'] }}:</label>
					<div class="col-sm-8">
						<input type="password" name="editpassword" class="form-control" />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['oldpass'] }}:</label>
					<div class="col-sm-8">
						<input type="password" name="oldpass" value="" class="form-control" />
						<span class="form-text text-muted">{{ lang.uprofile['oldpass#desc'] }}</span>
					</div>
				</div>
				{% if (flags.photoAllowed) %}
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['photo'] }}:</label>
					<div class="col-sm-8">
						<input type="file" name="newphoto" class="upload" />
					</div>
					{% if (user.flags.hasPhoto) %}
						<a href="{{ user.photo }}" target="_blank"><img src="{{ user.photo_thumb }}" style="margin: 5px; border: 0px; max-width: 80px; max-height: 80px;" alt=""/></a><br />
						<input type="checkbox" name="delphoto" id="delphoto" />&nbsp;{{ lang.uprofile['delete'] }}
					{% endif %}
				</div>
				{% else %}
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['photo'] }}:</label>
					{{ lang.uprofile['photos_denied'] }}
				</div>
				{% endif %}
				{% if (flags.avatarAllowed) %}
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['avatar'] }}:</label>
					<div class="col-sm-8">
						<input type="file" name="newavatar" class="upload" />
					</div>
					{% if (user.flags.hasAvatar) %}
						<img src="{{ user.avatar }}" style="margin: 5px; border: 0px; max-width: 80px; max-height: 80px;" alt=""/><br />
						<input type="checkbox" name="delavatar" id="delavatar" />&nbsp;{{ lang.uprofile['delete'] }}
					{% endif %}
				</div>
				{% else %}
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">{{ lang.uprofile['avatar'] }}:</label>
					{{ lang.uprofile['avatars_denied'] }}
				</div>
				{% endif %}
				{% if pluginIsActive('xfields') %}{{ plugin_xfields_0 }}{% endif %}
				<div class="clearfix"></div>
				<div class="label">
					<input type="submit" onclick="return validate_form();" value="{{ lang.uprofile['save'] }}" class="btn btn-success">
				</div>
			</form>
		</div>
	</div>
</div>