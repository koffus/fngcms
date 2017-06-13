<div class="widget widget-pages">
	<h4 class="widget-title">{{ lang['top_users:widget-title'] }}</h4>
	<dl class="row">
		{% for entry in entries %}
			<dt class="col-sm-4">
				{% if (entry.use_avatars) %}<img src="{{entry.avatar_url}}" alt="{{entry.name}}" class="img-thumbnail" />{% endif %}
			</dt>
			<dd class="col-sm-8">
				<a href="{{entry.link}}" title="{{entry.name}}" class="text-uppercase">{{entry.name}}</a>
				<p>{{ lang['comments'] }} <span class="pull-right">{{entry.com}}</span>
				<br />
				{{ lang['news'] }} <span class="pull-right">{{entry.news}}</span></p>
			</dd>
		{% endfor %}
	</dl>
</div>


