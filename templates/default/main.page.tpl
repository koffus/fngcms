{% if pluginIsActive('breadcrumbs') %}<section class="container section">{{ callPlugin('breadcrumbs.show', {}) }}</section>{% endif %}

<section class="container section">
	<div class="row">

		<main class="col-lg-8">
			{{ mainblock }}
		</main>

		<aside class="sidebar col-lg-3 offset-lg-1">

            {% if pluginIsActive('gallery') %}
                {{ plugin_gallery_category }}
                {{ plugin_gallery_widget_gallery }}
			{% endif %}

			{% if pluginIsActive('archive') %}
				{{ callPlugin('archive.show', {'maxnum' : 12, 'counter' : 1, 'tcounter' : 1, 'template': 'archive', 'cacheExpire': 60}) }}
			{% endif %}

			{% if pluginIsActive('calendar') %}
				{{ callPlugin('calendar.show', {}) }}
			{% endif %}
            
			{% if pluginIsActive('bookmarks') %}
				{{ plugin_bookmarks }}
			{% endif %}

			{% if pluginIsActive('lastcomments') %}
				{{ plugin_lastcomments }}
			{% endif %}

			{% if pluginIsActive('favorites') %}
				{{ plugin_favorites }}
			{% endif %}

			{% if pluginIsActive('voting') %}
				{{ voting }}
			{% endif %}

			{% if pluginIsActive('tags') %}
				{{ plugin_tags }}
			{% endif %}

			{% if pluginIsActive('jchat') %}
				{{ plugin_jchat }}
			{% endif %}

			{% if pluginIsActive('xnews') %}
				<div class="widget widget-popular">
					<h4 class="widget-title">{{ lang.theme.popular_article }}</h4>
					<ul class="nav tabs" id="myTab">
						<li class="nav-item"><a class="nav-link active" id="day_1" data-toggle="tab" href="#tab-1">{{ lang.theme.day_1 }}</a></li>
						<li class="nav-item"><a class="nav-link" id="day_2" data-toggle="tab" href="#tab-2">{{ lang.theme.day_2 }}</a></li>
						<li class="nav-item"><a class="nav-link" id="day_3" data-toggle="tab" href="#tab-3">{{ lang.theme.day_3 }}</a></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane fade active show" id="tab-1">
							{{ callPlugin('xnews.show', {'order' : 'viewed', 'count': '5', 'template' : 'xnews1', 'maxAge' : '7'}) }}
						</div>
						<div class="tab-pane fade" id="tab-2">
							{{ callPlugin('xnews.show', {'order' : 'viewed', 'count': '5', 'template' : 'xnews1', 'maxAge' : '30'}) }}
						</div>
						<div class="tab-pane fade" id="tab-3">
							{{ callPlugin('xnews.show', {'order' : 'viewed', 'count': '5', 'template' : 'xnews1'}) }}
						</div>
					</div>
				</div>
			{% endif %}

			{% if pluginIsActive('switcher') %}
				{{ plugin_switcher }}
			{% endif %}

			{% if pluginIsActive('top_users') %}
				{{ callPlugin('top_users.show', {'number' : 12, 'mode' : 'news', 'template': 'top_users', 'cacheExpire': 60}) }}
			{% endif %}

			<div class="widget widget-pages">
				<h4 class="widget-title">Страницы</h4>
				<ul>
					<li class="page-item"><a href="#">О сайте</a></li>
					<li class="page-item"><a href="#">О сайте</a></li>
					<li class="page-item"><a href="#">О сайте</a></li>
				</ul>
			</div>

			<div class="widget widget-text">
				<h4 class="widget-title">Sed ut perspiciatis</h4>
				<div class="textwidget">Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh.</div>
			</div>

		</aside>

	</div>
</section>