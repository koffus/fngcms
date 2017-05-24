{% if pluginIsActive('breadcrumbs') %}<section class="container section">{{ callPlugin('breadcrumbs.show', {}) }}</section>{% endif %}

<section class="container section">
	<div class="row">

		<main class="col-lg-8">
			{{ mainblock }}
		</main>

		<aside class="col-lg-3 offset-lg-1">
			{{ search_form }}

			{% if pluginIsActive('xnews') %}
				<div class="widget widget-popular">
					<h4 class="widget-title">{{ lang.theme.popular_article }}</h4>
					<ul class="nav tabs tabs-full" id="myTab">
						<li class="nav-item"><a class="nav-link active" id="day_1" data-toggle="tab" href="#tab-1">{{ lang.theme.day_1 }}</a></li>
						<li class="nav-item"><a class="nav-link" id="day_2" data-toggle="tab" href="#tab-2">{{ lang.theme.day_2 }}</a></li>
						<li class="nav-item"><a class="nav-link" id="day_3" data-toggle="tab" href="#tab-3">{{ lang.theme.day_3 }}</a></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane fade active show" id="tab-1">
							{{ callPlugin('xnews.show', {'order' : 'viewed', 'count': '5', 'template' : 'xnews1', 'maxAge' : '1'}) }}
						</div>
						<div class="tab-pane fade" id="tab-2">
							{{ callPlugin('xnews.show', {'order' : 'viewed', 'count': '5', 'template' : 'xnews1', 'maxAge' : '7'}) }}
						</div>
						<div class="tab-pane fade" id="tab-3">
							{{ callPlugin('xnews.show', {'order' : 'viewed', 'count': '5', 'template' : 'xnews1'}) }}
						</div>
					</div>
				</div>
			{% endif %}

			{% if pluginIsActive('archive') %}
			<div class="block archive-block">
				{{ callPlugin('archive.show', {'maxnum' : 12, 'counter' : 1, 'tcounter' : 1, 'template': 'archive', 'cacheExpire': 60}) }}
			</div>
			{% endif %}

			{% if pluginIsActive('calendar') %}
				{{ callPlugin('calendar.show', {}) }}
			{% endif %}

			{% if pluginIsActive('lastcomments') %}
				{{ plugin_lastcomments }}
			{% endif %}

			{% if pluginIsActive('voting') %}
				{{ voting }}
			{% endif %}

			{% if pluginIsActive('tags') %}
				{{ plugin_tags }}
			{% endif %}

			{% if pluginIsActive('switcher') %}
				{{ switcher }}
			{% endif %}

			{% if pluginIsActive('top_active_users') %}
			<div class="block popular-authors-block">
				<h4 class="widget-title">{{ lang.theme.popular_authors }}</h4>
				{{ callPlugin('top_active_users.show', {'number' : 12, 'mode' : 'news', 'template': 'top_active_users', 'cacheExpire': 60}) }}
			</div>
			{% endif %}

		</aside>

	</div>
</section>