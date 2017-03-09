<h3>{{ lang['archive:plugin_title'] }}</h3>
<ul>
	{% for entry in entries %}
		<li><a href="{{entry.link}}">{{entry.title}} {% if (entry.counter) %}({{ entry.cnt }}{{ entry.ctext }}){% endif %}</a></li>
	{% endfor %}
</ul>
