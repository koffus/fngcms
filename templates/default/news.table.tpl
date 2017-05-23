{% if data %}
	<div class="row">
	{% for entry in data %}
		<div class="{% if isHandler('news:main') %}col-lg-4 col-md-4{% endif %}  col-sm-12 col-xs-12">{{ entry }}</div>
	{% endfor %}
	</div>
{% else %}
	<div class="alert alert-info">
		<strong>{{ lang.notifyWindowInfo }}</strong>
		{{ lang.msgi_no_news }}
	</div>
{% endif %}

{{ pagination }}
