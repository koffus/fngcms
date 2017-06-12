<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang.home }}</a></li>
	<li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
	<li class="active">{{ plugin }}</li>
</ul>

<!-- Info content -->
<div class="page-main">

	<form id="postForm" name="form" action="admin.php?mod=extra-config&plugin={{ plugin }}&action=commit" method="post">
		<input type="hidden" name="token" value="{{ token }}" />

		<div class="well">{{ description }}</div>

		{% for entry in entries %}
			{% if entry.type == 'flat' %}
				{{ entry.input }}
			{% else %}
				{% if entry.flags.group %}
					<fieldset>
						<legend>{{ entry.groupTitle }}{% if entry.flags.toggle %} <a href="#" title="{{ lang['group.toggle'] }}" class="adm-group-toggle"><i class="fa fa-caret-square-o-down"></i></a>{% endif %}</legend>
						<div class="adm-group-content"{% if entry.flags.toggle %} style="display:none;"{% endif %}>
							{% for subentry in entry.subentries %}
							<div class="form-group">
								<div class="row">
									<div class="col-sm-8">
										{{ subentry.title }}
										{% if subentry.flags.descr %}<span class="help-block">{{ subentry.descr }}</span>{% endif %}
										{% if subentry.flags.error %}<span class="help-block">{{ subentry.error }}</span>{% endif %}
									</div>
									<div class="col-sm-4">
										{{ subentry.input }}
									</div>
								</div>
							</div>
							{% endfor %}
						</div>
					</fieldset>
				{% else %}
					<div class="form-group">
						<div class="row">
							<div class="col-sm-8">
								{{ entry.title }}
								{% if entry.flags.descr %}<span class="help-block">{{ entry.descr }}</span>{% endif %}
								{% if entry.flags.error %}<span class="help-block">{{ entry.error }}</span>{% endif %}
							</div>
							<div class="col-sm-4">
								{{ entry.input }}
							</div>
						</div>
					</div>
				{% endif %}
			{% endif %}
		{% endfor %}

		<div class="well text-center">
			<input type="submit" value="{{ lang.commit_change }}" class="btn btn-success" />
		</div>
	</form>

</div>

<script>
var form = document.getElementById('postForm');

// HotKeys to this page
document.onkeydown = function(e) {
	e = e || event;
	if (e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
		form.submit();
		return false;
	}
}
</script>