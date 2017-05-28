<h2 class="section-title">{{ lang['pm:set'] }}</h2>

<form method="POST" action="{{ php_self }}?action=set">

	<div class="row">
		<div class="col-md-4">
			<div class="list-group">
				<a href="{{ php_self }}?action=write" class="list-group-item list-group-item-action">{{ lang['pm:write'] }}</a>
				<a href="{{ home }}/plugin/pm/" class="list-group-item list-group-item-action"><i class="fa fa-inbox"></i>&nbsp;{{ lang['pm:inbox'] }}</a>
				<a href="{{ home }}/plugin/pm/?action=outbox" class="list-group-item list-group-item-action"><i class="fa fa-envelope-o"></i>&nbsp;{{ lang['pm:outbox'] }}</a>
				<a href="{{ php_self }}?action=set" class="list-group-item list-group-item-action active"> <i class="fa fa-cog"></i>&nbsp;{{ lang['pm:set'] }}</a>
			</div>
		</div>

		<div class="col-md-8">
			<div class="form-group">
				<div class="card card-block">
					<label><input type="checkbox" name="email" id="email" {{ checked }} />&nbsp;{{ lang['pm:email_set'] }}</label>
				</div>
			</div>
			<div class="form-group">
				<input type="submit" class="btn btn-success" value="{{ lang['pm:send'] }}" />
			</div>
		</div>
	</div>
</form>
