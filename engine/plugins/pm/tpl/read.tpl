<h2 class="section-title">{{ subject }} {% if (ifinbox) %}от{% endif %} {% if not (ifinbox) %}для{% endif %} {{ author }} {{ pmdate|cdate() }}</h2>


<form method="POST" action="{{ php_self }}?action=delete&pmid={{ pmid }}&location={{ location }}">

	<div class="row">
		<div class="col-md-4">
			<div class="list-group">
				<a href="{{ php_self }}?action=write" class="list-group-item list-group-item-action">{{ lang['pm:write'] }}</a>
				<a href="{{ home }}/plugin/pm/" class="list-group-item list-group-item-action {% if (ifinbox) %}active{% endif %}"><i class="fa fa-inbox"></i>&nbsp;{{ lang['pm:inbox'] }}</a>
				<a href="{{ home }}/plugin/pm/?action=outbox" class="list-group-item list-group-item-action {% if not (ifinbox) %}active{% endif %}"><i class="fa fa-envelope-o"></i>&nbsp;{{ lang['pm:outbox'] }}</a>
				<a href="{{ php_self }}?action=set" class="list-group-item list-group-item-action"> <i class="fa fa-cog"></i>&nbsp;{{ lang['pm:set'] }}</a>
			</div>
		</div>

		<div class="col-md-8">
			<div class="form-group">
				<div class="card"><div class="card-block"><p class="card-text">{{ content }}</p></div></div>
			</div>
			<div class="form-group">
				<input type="submit" value="{{ lang['pm:delete_one'] }}" class="btn btn-danger">
				{% if (ifinbox == 1) %}
				<a href="{{ php_self }}?action=reply&pmid={{ pmid }}" class="btn btn-primary">{{ lang['pm:reply'] }}</a>
				{% endif %}
			</div>
		</div>
	</div>
</form>

