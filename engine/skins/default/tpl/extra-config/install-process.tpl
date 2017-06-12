<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
	<li class="active">{{ mode_text }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<form action="admin.php?mod=extras" method="post">
		<input type="hidden" name="token" value="{{ token }}" />
		<input type="hidden" name="enable" value="{{ plugin }}" />
		
		<div class="panel panel-default panel-table">
			<div class="panel-heading">
				<h3>{{ plugin }}</h3>
			</div>
			<div class="panel-body">
				{% if entries %}
					<table class="table table-condensed">
						{% for entry in entries %}
							<tr>
								<td>
									{{ entry.title }}
									{% if entry.descr %}<br /><code>{{ entry.descr }}</code>{% endif %}
								</td>
								<td>{{ entry.result }}</td>
							</tr>
						{% endfor %}
					</table>
				{% endif %}
			</div>
			<div class="panel-footer">
				<h4>{{ msg }}</h4>
			</div>
		</div>
		<div class="well text-center">
		{% if flags.enable %}
			<button type="submit" class="btn btn-success">{{ lang['switch_on'] }}</button>
		{% else %}
			<a href="admin.php?mod=extras" class="btn btn-primary">{{ lang['extras'] }}</a>
		{% endif %}
		</div>
	</form>
</div>
