{% if (handler == 'by.category') %}
    <small class="pull-right">{{ count }}</small>
    <h2 class="section-title">{{ category.name }}</h2>
    {% include 'news-order.tpl' %}
    {% if (pages.current == 1) %}
        {% if category.icon.purl %}<img src="{{ category.icon.purl }}" />{% endif %}
        {% if category.info %}{{ category.info }}<hr class="alert-info" />{% endif %}
    {% endif %}

{% else %}
     <small class="pull-right">{{ count }}</small>
     <h2 class="section-title">{{ lang.news }}</h2>
{% endif %}

<div class="row">
{% for entry in data %}
    <div class="{% if isHandler('news:main') and (pages.current == 1) %}col-lg-4 col-md-4{% endif %} col-sm-12 col-xs-12">{{ entry }}</div>
{% else %}
    <div class="col-md-12">
        <div class="alert alert-info">
            <strong>{{ lang.notifyWindowInfo }}</strong>
            {{ lang.msgi_no_news }}
        </div>
    </div>
{% endfor %}
</div>

{{ pagination }}