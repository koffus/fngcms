<!-- Navigation bar -->
<ul class="breadcrumb">
    <li><a href="admin.php">{{ lang.home }}</a></li>
    <li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
    <li class="active">{{ plugin }}</li>
</ul>

<!-- Info content -->
<div class="page-main">

    {% if navigation %}
        <ul class="nav nav-tabs">
        {% for link in navigation %}
            <li class="{{ link.class }}"><a href="{{ link.href }}" {{ link.data }}>{{ link.title }}</a></li>
        {% endfor %}
        </ul>
        <br/>
    {% endif %}

    {% if description %}<div class="well">{{ description }}</div>{% endif %}

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

    <form id="postForm" name="form" action="{% if action %}{{ action }}{% else %}admin.php?mod=extra-config&plugin={{ plugin }}&action=commit{% endif %}" method="post" class="form-horizontal">
        <input type="hidden" name="token" value="{{ token }}" />

        <div id="configTabs" class="tab-content">
        {% for entry in entries %}
            {% if entry.flags.group %}
                <fieldset>
                    <legend>{{ entry.groupTitle }}{% if entry.flags.toggle %} <a href="#" title="{{ lang['group.toggle'] }}" class="adm-group-toggle"><i class="fa fa-caret-square-o-down"></i></a>{% endif %}</legend>
                    <div class="adm-group-content"{% if entry.flags.toggle %} style="display:none;"{% endif %}>
                        {% for subentry in entry.subentries %}
                            {% if ('flat' == subentry.type or 'hidden' == subentry.type) %}
                                {{ subentry.input }}
                            {% else %}
                            <div class="form-group">
                                <div class="col-sm-8">
                                    {{ subentry.title }}
                                    {% if subentry.descr %}<span class="help-block">{{ subentry.descr }}</span>{% endif %}
                                    {% if subentry.error %}<span class="help-block">{{ subentry.error }}</span>{% endif %}
                                </div>
                                <div class="col-sm-4">
                                    {{ subentry.input }}
                                </div>
                            </div>
                            {% endif %}
                        {% endfor %}
                    </div>
                </fieldset>
            {% else %}
                {% if (('flat' == entry.type) or ('hidden' == entry.type)) %}
                    {{ entry.input }}
                {% else %}
                    <div class="form-group">
                        <div class="col-sm-8">
                            {{ entry.title }}
                            {% if entry.descr %}<span class="help-block">{{ entry.descr }}</span>{% endif %}
                            {% if entry.error %}<span class="help-block">{{ entry.error }}</span>{% endif %}
                        </div>
                        <div class="col-sm-4">
                            {{ entry.input }}
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
                        <button type="submit" name="submit" value="true" class="{% if link.class %}{{ link.class }}{% else %}btn btn-success{% endif %}">{{ lang.commit_change }}</button>
                    {% elseif 'reinstall' == link.type %}
                        <a href="admin.php?mod=extra-config&plugin={{ plugin }}&stype=install" class="{% if link.class %}{{ link.class }}{% else %}btn btn-default{% endif %}">{{ lang['btn.reinstall'] }}</a>
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