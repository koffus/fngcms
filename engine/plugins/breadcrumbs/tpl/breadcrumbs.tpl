<div class="breadcrumb-container" xmlns:v="http://rdf.data-vocabulary.org/#">
	<ul class="breadcrumb">
		{% for loc in location %}
			{% if (loop.index)==1 %}
				<li typeof="v:Breadcrumb">
					<i class="fa fa-home pr-10"></i>&nbsp;
					<a href="{{ loc.url }}" rel="v:url" property="v:title">{{ home_title }}</a>
				</li>
			{% else %}
				<li typeof="v:Breadcrumb">
					<a href="{{ loc.url }}" rel="v:url" property="v:title">{{ loc.title }}</a>
				</li>
			{% endif %}
		{% endfor %}
		{% if (location_last) %}
		<li typeof="v:Breadcrumb" class="active">{{ location_last }}</li>
		{% endif %}
	</ul>
</div>