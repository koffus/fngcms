<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang.home }}</a></li>
	<li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
	<li class="active">feedback</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<!-- List of forms: BEGIN -->
	<div class="panel panel-default panel-table">
		<div class="panel-heading text-right">
			<a href="admin.php?mod=extra-config&plugin=feedback&action=addform" title="Создать новую форму" class="btn btn-success"><i class="fa fa-plus"></i> </a>
		</div>
		<div class="panel-body table-responsive">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th>Код</th>
						<th>ID формы</th>
						<th>Название формы</th>
						<th>Привязка к новостям</th>
						<th>Активна</th>
						<th class="text-right">{{ lang['action'] }}</th>
					</tr>
				</thead>
				<tbody>
				{% if entries %}
					{% for entry in entries %}
					<tr>
						<td>{{ entry.id }}</td>
						<td>{{ entry.name }}</td>
						<td>{{ entry.title }}</td>
						<td>{{ lang['feedback:link_news.' ~ entry.link_news] }}</td>
						<td>{{ entry.flags.active ? lang['yesa'] : lang['noa'] }}</td>
						<td class="text-right">
							<div class="btn-group">
								<a href="{{ entry.linkEdit}}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
							{% if (entry.flags.active) %}
								<a href="#" onclick="alert('{{ lang['feedback:active_nodel'] }}');" class="btn btn-danger">
							{% else %}
								<a href="{{ entry.linkDel }}" onclick="return confirm('{{ lang['sure_del'] }}');" class="btn btn-danger">
							{% endif %}<i class="fa fa-trash-o"></i>
								</a>
							</div>
						</td>
					</tr>
					{% endfor %}
				{% else %}
					<td colspan="6">{{ lang['not_found'] }}</td>
				{% endif %}
				</tbody>
			</table>
		</div>
	</div>
	<!-- List of forms: END -->
</div>