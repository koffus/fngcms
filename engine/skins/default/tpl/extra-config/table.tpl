<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang.home }}</a></li>
	<li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
	<li class="active">{{ plugin }}</li>
</ul>

<!-- Info content -->
<div class="page-main">

	<form name="form" action="admin.php?mod=extra-config&plugin={{ plugin }}&action=commit" method="post">
		<input type="hidden" name="token" value="{{ token }}" />

		<div class="well">{{ description }}</div>

		{% for entry in entries %}
			{% if entry.flags.group %}
				<legend>{{ entry.groupTitle }}</legend>
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
		{% endfor %}

		<div class="well text-center">
			<input type="submit" value="{{ lang.commit_change }}" class="btn btn-success" />
		</div>
	</form>

</div>