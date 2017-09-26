<div class="widget widget-xnews">
    <div class="widget-header">
        <h4 class="widget-title">{{ lang['news'] }}</h4>
    </div>
    <div class="widget-body">
        <dl class="row">
            {% for item in items %}
                <dt class="col-sm-4">
                    <img src="{{ (item.embed.imgCount > 0) ? item.embed.images[0] : tpl_url ~ '/img/img-none.png' }}" alt="{{ item.title }}" class="img-thumbnail" />
                </dt>
                <dd class="col-sm-8">
                    <a href="{{ item.url.full }}">{{ item.title|truncateHTML(70,'...') }}</a>
                    <p>
                        <small>
                            <i class="fa fa-calendar"></i>&nbsp;{{ item.dateStamp|cdate }}
                        </small>
                    </p>
                </dd>
            {% endfor %}
        </dl>
    </div>
</div>