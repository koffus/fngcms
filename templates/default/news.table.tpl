{% if (handler == 'by.category') %}
	<small class="pull-right">{{ count }}</small>
	<h2 class="section-title">{{ category.name }}</h2>
	{% if (pages.current == 1) %}
		{% if category.icon.purl %}<img src="{{ category.icon.purl }}" />{% endif %}
		{% if category.info %}{{ category.info }}<hr />{% endif %}
	{% endif %}

	{% include 'news-order.tpl' %}

{% else %}
	 <small class="pull-right">{{ count }}</small>
	 <h2 class="section-title">{{ lang.news }}</h2>
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

{% if not(isHandler('news:main') and (pages.current == 1)) %}
	<a href="#" onclick="nextPage(); return false;" class="btn btn-success pull-right" id="next-page" style="cursor: pointer">Показать еще</a>
	<script type="text/javascript">
		function nextPage() {
			var nextPage = $('#ajax-next-page a').attr('href');
			ngShowLoading("");
			if (nextPage !== undefined) {
				$.ajax({ url: nextPage, success: function(data) {
					$('#ajax-next-page, #next-page, #nav-page').remove();
					ngHideLoading("");
					$('main').append($('main', data).html());
				}})
			}
		};
		$(function() {
			var nextPage = $('#ajax-next-page a').attr('href');
			if (nextPage === undefined) {
				$('#next-page').remove();
			}
		});
	</script>
{% endif %}

{{ pagination }}