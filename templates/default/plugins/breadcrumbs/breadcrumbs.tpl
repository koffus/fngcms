<div class="breadcrumb-container" xmlns:v="http://rdf.data-vocabulary.org/#">
	<ol class="breadcrumb">
		{% for loc in location %}
			{% if (loop.index)==1 %}
				<li typeof="v:Breadcrumb" class="breadcrumb-item">
					<i class="fa fa-home pr-10"></i>&nbsp;
					<a href="{{ loc.url }}" rel="v:url" property="v:title">{{ home_title }}</a>
				</li>
			{% else %}
				<li typeof="v:Breadcrumb" class="breadcrumb-item">
					<a href="{{ loc.url }}" rel="v:url" property="v:title">{{ loc.title }}</a>
				</li>
			{% endif %}
		{% endfor %}
		{% if (location_last) %}
		<li typeof="v:Breadcrumb" class="breadcrumb-item active">{{ location_last }}</li>
		{% endif %}
	</ol>
</div>