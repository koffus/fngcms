<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
	<li class="active">{{ lang['с_с:page_title'] }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<div class="alert alert-warning">{{ lang['с_с:alert_warning'] }}</div>

	<table class="table table-condensed">
		<thead>
			<tr>
				<th>{{ lang['с_с:id'] }}</th>
				<th>{{ lang['с_с:del_config'] }}</th>
			</tr>
		</thead>
		<tbody>
		{% for entry in entries %}
			<tr>
				<td>{{ entry.id }}</td>
				<td>{{ entry.conf }}</td>
			</tr>
		{% endfor %}
		</tbody>
	</table>
</div>