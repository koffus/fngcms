<tr>
	<td>{{ name }}</td>
	<td>{{ description }}</td>
	<td>{{ type }}</td>
	<td>{{ state }}</td>
	<td>{{ online }}</td>
	<td class="text-right">
		<a href="admin.php?mod=extra-config&plugin=ads_pro&action=edit&id={{ id }}" title="{{ lang['ads_pro:button_edit'] }}" class="btn btn-default">
			<i class="fa fa-pencil"></i>
		</a>
		<a href="admin.php?mod=extra-config&plugin=ads_pro&action=move_up&id={{ id }}" title="{{ lang['ads_pro:button_up'] }}" class="btn btn-default">
			<i class="fa fa-arrow-up"></i>
		</a>
		<a href="admin.php?mod=extra-config&plugin=ads_pro&action=move_down&id={{ id }}" title="{{ lang['ads_pro:button_down'] }}" class="btn btn-default">
			<i class="fa fa-arrow-down"></i>
		</a>
		<a href="admin.php?mod=extra-config&plugin=ads_pro&action=dell&id={{ id }}" title="{{ lang['ads_pro:button_dell'] }}" class="btn btn-danger">
			<i class="fa fa-trash-o"></i>
		</a>
	</td>
</tr>