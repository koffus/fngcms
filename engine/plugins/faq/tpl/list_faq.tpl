<div class="panel panel-default panel-table">
	<div class="panel-heading text-right">
		<a href="admin.php?mod=extra-config&plugin=faq&action=add_faq" class="btn btn-success"><i class="fa fa-plus"></i></a>
	</div>
	<form name="check_faq" action="{{ home }}/engine/admin.php?mod=extra-config&plugin=faq&action=modify" method="post">
		<div class="panel-body table-responsive">
				<table class="table table-condensed">
					<thead>
						<tr>
							<th><input type="checkbox" class="select-all" title="{{ lang.select_all }}"></th>
							<th>ID</th>
							<th>Вопрос</th>
							<th>Ответ</th>
							<!--th><i class="fa fa-thumbs-o-up"></i></th>
							<th><i class="fa fa-thumbs-o-down"></i></th-->
							<th>{{ lang['state'] }}</th>
						</tr>
					</thead>
					<tbody>
					{% for entry in entries %}
						<tr>
							<td><input type="checkbox" name="selected_faq[]" value="{{ entry.id }}" /></td>
							<td><a href="?mod=extra-config&plugin=faq&action=edit_faq&id={{ entry.id }}"/>{{ entry.id }}</a></td>
							<td>{{ entry.question }}</td>
							<td>{{ entry.answer }}</td>
							<!--td>{{ entry.rat_p }}</td>
							<td>{{ entry.rat_m }}</td-->
							<td>{% if (entry.active == "1") %}<i class="fa fa-check text-success"></i>{% else %}<i class="fa fa-times text-danger"></i>{% endif %}
							</td>
						</tr>
					{% else %}
						<tr><td colspan="5">{{ lang['not_found'] }}</td></tr>
					{% endfor %}
					</tbody>
				</table>
			</div>
		<div class="panel-footer">
			<div class="row">
				<div class="col col-md-4">
					<div class="input-group">
						<select name="subaction" class="form-control">
							<option value="">-- Действие --</option>
							<option value="mass_approve">Активировать</option>
							<option value="mass_forbidden">Деактивировать</option>
							<option value="" style="background-color: #E0E0E0;" disabled="disabled">===================</option>
							<option value="mass_delete">Удалить</option>
						</select>
						<span class="input-group-btn">
							<button type="submit" class="btn btn-default">Применить</button>
						</span>
					</div>
				</div>
				<div class="col col-md-8 text-right">{{ pagesss }}</div>
			</div>
		</div>
	</form>
</div>
