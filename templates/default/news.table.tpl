<h2 class="section-title">{{ category.name }}</h2>
{% if (handler == 'by.category') and (pages.current == 1) %}
	{% if category.icon.purl %}<img src="{{ category.icon.purl }}" />{% endif %}
	{% if category.info %}{{ category.info }}<hr />{% endif %}
{% endif %}

{% if data %}
	<div class="row">
	{% for entry in data %}
		<div class="{% if isHandler('news:main') and (pages.current == 1) %}col-lg-4 col-md-4{% endif %} col-sm-12 col-xs-12">{{ entry }}</div>
	{% endfor %}
	</div>
{% else %}
	<div class="alert alert-info">
		<strong>{{ lang.notifyWindowInfo }}</strong>
		{{ lang.msgi_no_news }}
	</div>
{% endif %}

{{ pagination }}
