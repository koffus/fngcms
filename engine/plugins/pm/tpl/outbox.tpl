<h2 class="section-title">{{ lang['pm:outbox'] }}</h2>

<form name="form" method="POST" action="{{ php_self }}?action=delete">

	<div class="row">
		<div class="col-md-4">
			<div class="list-group">
				<a href="{{ php_self }}?action=write" class="list-group-item list-group-item-action">{{ lang['pm:write'] }}</a>
				<a href="{{ home }}/plugin/pm/" class="list-group-item list-group-item-action"><i class="fa fa-inbox"></i>&nbsp;{{ lang['pm:inbox'] }}</a>
				<a href="{{ home }}/plugin/pm/?action=outbox" class="list-group-item list-group-item-action active"><i class="fa fa-envelope-o"></i>&nbsp;{{ lang['pm:outbox'] }}</a>
				<a href="{{ php_self }}?action=set" class="list-group-item list-group-item-action"> <i class="fa fa-cog"></i>&nbsp;{{ lang['pm:set'] }}</a>
			</div>
		</div>

		<div class="col-md-8">
			<table class="table table-sm">
				<tr>
					<td><input type="checkbox" title="{{ lang['select_all'] }}" class="select-all" /></td>
					<td>{{ lang['pm:date'] }}</td>
					<td>{{ lang['pm:subject'] }}</td>
					<td>{{ lang['pm:too'] }}</td>
				</tr>
				{% for entry in entries %}
				<tr>
					<td><input type="checkbox" name="selected_pm[]" value="{{ entry.pmid }}" /></td>
					<td>{{ entry.pmdate|cdate }}</td>
					<td><a href="{{ php_self }}?action=read&pmid={{ entry.pmid }}&location=outbox">{{ entry.subject }}</a></td>
					<td>{{ entry.link }}</td>
				</tr>
				{% endfor %}
			</table>
			<button type="submit" class="btn btn-danger pull-right" title="{{ lang['pm:delete'] }}"><i class="fa fa-trash"></i></button>
			<nav>
				<ul class="pagination">
					{{ pagination }}
				</ul>
			</nav>
		</div>
	</div>
</form>
