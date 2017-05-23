<ul class="list-unstyled">{% if entries %}
	{% for entry in entries %}
		<li>{{ entry }}</li>
	{% endfor %}
	
{% else %}
	<li class="text-info">{{ lang['msgi_no_news'] }}</li>
{% endif %}
</ul>