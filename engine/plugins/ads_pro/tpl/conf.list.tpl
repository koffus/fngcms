<table class="table table-condensed">
	<thead>
		<tr>
			<th>{{ lang['ads_pro:name'] }}</th>
			<th>{{ lang['ads_pro:description'] }}</th>
			<th>{{ lang['ads_pro:type'] }}</th>
			<th>{{ lang['ads_pro:state'] }}</th>
			<th>{{ lang['ads_pro:online'] }}</th>
			<th class="text-right">{{ lang['action'] }}</th>
		</tr>
	</thead>
	<tbody>
		{% for entry in entries %}
		<tr>
			<td>{{ entry.name }}</td>
			<td>{{ entry.description }}</td>
			<td>{{ entry.type }}</td>
			<td>{{ entry.state }}</td>
			<td>{{ entry.online }}</td>
			<td class="text-right">
				<div class="btn-group">
					<a href="admin.php?mod=extra-config&plugin=ads_pro&action=edit&id={{ entry.id }}" title="{{ lang['ads_pro:btn.edit'] }}" class="btn btn-default"><i class="fa fa-pencil"></i></a>
					<a href="admin.php?mod=extra-config&plugin=ads_pro&action=move_up&id={{ entry.id }}" title="{{ lang['ads_pro:btn.up'] }}" class="btn btn-default"><i class="fa fa-arrow-up"></i></a>
					<a href="admin.php?mod=extra-config&plugin=ads_pro&action=move_down&id={{ entry.id }}" title="{{ lang['ads_pro:btn.down'] }}" class="btn btn-default"><i class="fa fa-arrow-down"></i></a>
					<a href="admin.php?mod=extra-config&plugin=ads_pro&action=dell&id={{ entry.id }}" title="{{ lang['ads_pro:btn.dell'] }}" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
				</div>
			</td>
		</tr>
		{% else %}
		<tr><td colspan="6"><p>{{ lang['not_found'] }}</p></td></tr>
		{% endfor %}
	</tbody>
</table>
