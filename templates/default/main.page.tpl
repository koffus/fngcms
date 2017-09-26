{% if pluginIsActive('breadcrumbs') %}<section class="container section">{{ callPlugin('breadcrumbs.show', {}) }}</section>{% endif %}

<section class="container section">
    <div class="row">

        <main class="col-lg-8">
            {{ mainblock }}
        </main>

        <aside class="sidebar col-lg-3 ml-auto">

            {% if pluginIsActive('gallery') %}
                {{ plugin_gallery_category }}
                {{ plugin_gallery_widget }}
            {% endif %}

            {% if pluginIsActive('rss_import') %}
                {{ plugin_rss_import_widget }}
            {% endif %}

            {% if pluginIsActive('tags') %}
                {{ plugin_tags }}
            {% endif %}

            {% if pluginIsActive('archive') %}
                {{ plugin_archive }}
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

            {% if pluginIsActive('jchat') %}
                {{ plugin_jchat }}
            {% endif %}

            {% if pluginIsActive('xnews') %}
                {{ callPlugin('xnews.show', {'order' : 'viewed', 'count': '5', 'skin' : 'basic'}) }}
            {% endif %}

            {% if pluginIsActive('switcher') %}
                {{ plugin_switcher }}
            {% endif %}

            {% if pluginIsActive('top_users') %}
                {{ callPlugin('top_users.show', {'number' : 12, 'mode' : 'news', 'template': 'top_users', 'cache_expire': 60}) }}
            {% endif %}

            {% if pluginIsActive('k_online') %}
                {{ k_online }}
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