<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
	<li class="active">xfields</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<form action="admin.php?mod=extra-config&plugin=xfields" method="POST" class="form-horizontal">
		<input type="hidden" name="action" value="add" />
		<input type="hidden" name="section" value="{{ sectionID }}" />

		{% include 'plugins/xfields/tpl/navi.tpl' %}

		<br>
		<table class="table table-condensed">
			<thead>
			<tr>
				<th>ID поля</th>
				<th>Название поля</th>
				<th>Тип поля</th>
				<th>Возможные значения</th>
				<th>По умолчанию</th>
				<th>Обязательно</th>
				{% if (sectionID != 'tdata') %}
					<th>Блок</th>
				{% endif %}
				<th class="text-right">{{ lang['action'] }}</th>
			</tr>
			</thead>
			<tbody>
				{% for entry in entries %}
				<tr class="xListEntry{% if (entry.flags.disabled) %}Disabled{% endif %}">
					<td><a href="{{ entry.link }}">{{ entry.name }}</a>{% if (sectionID == 'users') and (entry.flags.regpage )%} <span title="{{ lang.xfconfig['show_regpage'] }}">[<b><font color="red">R</font></b>]{% endif %}</span></td>
					<td>{{ entry.title }}</td>
					<td>{{ entry.type }}</td>
					<td>{{ entry.options }}</td>
					<td>{% if (entry.flags.default) %}{{ entry.default }}{% else %}<font color="red">не задано</font>{% endif %}</td>
					<td>{% if (entry.flags.required) %}<font color="red"><b>{{ lang['yesa'] }}</b></font>{% else %}{{ lang['noa'] }}{% endif %}</td>
					{% if (sectionID != 'tdata') %}
						<td>{{ entry.area }}</td>
					{% endif %}
					<td class="text-right td-nowrap">
						<a href="{{ entry.linkup }}" class="btn btn-default"><i class="fa fa-arrow-up"></i></a>
						<a href="{{ entry.linkdown }}" class="btn btn-default"><i class="fa fa-arrow-down"></i></a>
						<a  href="{{ entry.linkdel }}" onclick="return confirm('{{ lang.xfconfig['suretest'] }}');" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
					</td>
				</tr>
				{% else %}
					{{ lang.xfconfig['nof'] }}
				{% endfor %}
			</tbody>
		</table>
	
		<div class="well text-center">
			<input type="submit" value="{{ lang.xfconfig['add'] }}" class="btn btn-success" />
		</div>
	</form>

</div>