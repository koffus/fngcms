<!-- Navigation bar -->
<ul class="breadcrumb">
    <li><a href="admin.php">{{ lang.home }}</a></li>
    <li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
    <li class="active">{{ plugin }}</li>
</ul>

<!-- Info content -->
<div class="page-main">

    {% if description %}
        <div class="well">{{ description }}</div>
    {% else %}
        <div class="well">{{ lang.no_description }}</div>
    {% endif %}

    {% if dependence %}
        <div class="alert alert-warning">
            Для работы плагина требуется активация плагина:
            <ul>
            {% for item in dependence %}
                <li>{{ item }}</li>
            {% endfor %}
            </ul>
        </div>
    {% endif %}

    {% if navigation %}
        <ul class="nav nav-tabs">
        {% for link in navigation %}
            <li class="{{ link.class }}"><a href="{{ link.href }}" data-toggle="tab">{{ link.title }}</a></li>
        {% endfor %}
        </ul>
        <br/>
    {% endif %}

    <form id="postForm" name="form" action="admin.php?mod=extra-config&plugin={{ plugin }}" method="post">
        <input type="hidden" name="token" value="{{ token }}" />
        <input type="hidden" name="action" value="commit" />

        <div id="configTabs" class="tab-content">
        {% for entry in entries %}
            {% if entry.flags.group %}
                <fieldset>
                    <legend>{{ entry.groupTitle }}{% if entry.flags.toggle %} <a href="#" title="{{ lang['group.toggle'] }}" class="adm-group-toggle"><i class="fa fa-caret-square-o-down"></i></a>{% endif %}</legend>
                    <div class="adm-group-content"{% if entry.flags.toggle %} style="display:none;"{% endif %}>
                        {% for subentry in entry.subentries %}
                            {% if subentry.type == 'flat' %}
                                {{ subentry.input }}
                            {% else %}
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-8">
                                        {{ subentry.title }}
                                        {% if subentry.flags.descr %}<span class="help-block">{{ subentry.descr }}</span>{% endif %}
                                        {% if subentry.flags.error %}<span class="help-block">{{ subentry.error }}</span>{% endif %}
                                    </div>
                                    <div class="col-sm-4">
                                        {{ subentry.input }}
                                    </div>
                                </div>
                            </div>
                            {% endif %}
                        {% endfor %}
                    </div>
                </fieldset>
            {% else %}
                {% if entry.type == 'flat' %}
                    {{ entry.input }}
                {% else %}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-8">
                                {{ entry.title }}
                                {% if entry.flags.descr %}<span class="help-block">{{ entry.descr }}</span>{% endif %}
                                {% if entry.flags.error %}<span class="help-block">{{ entry.error }}</span>{% endif %}
                            </div>
                            <div class="col-sm-4">
                                {{ entry.input }}
                            </div>
                        </div>
                    </div>
                {% endif %}
            {% endif %}
        {% endfor %}
        </div>

        {% if submit %}
            <div class="well text-center">
                {% for link in submit %}
                    {% if 'default' == link.type %}
                        <input type="submit" value="{{ lang.commit_change }}" class="{% if link.class %}{{ link.class }}{% else %}btn btn-success{% endif %}" />
                    {% elseif 'clearCacheFiles' == link.type %}
                        <a href="admin.php?mod=extra-config&plugin={{ plugin }}&action=clearCacheFiles" class="{% if link.class %}{{ link.class }}{% else %}btn btn-primary{% endif %}">{{ lang['btn.clearCacheFiles'] }}</a>
                    {% else %}
                        <a href="{{ link.href }}" class="{% if link.class %}{{ link.class }}{% else %}btn btn-default{% endif %}">{{ link.title }}</a>
                    {% endif %}
                {% endfor %}
            </div>
        {% endif %}
    </form>

</div>

<script>
var form = document.getElementById('postForm');

// HotKeys to this page
document.onkeydown = function(e) {
    e = e || event;
    if (e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
        form.submit();
        return false;
    }
}
</script>