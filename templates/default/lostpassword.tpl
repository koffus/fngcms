<h2 class="section-title">{{ lang['lostpassword_title'] }}</h2>

<form name="lostpassword" action="{{ form_action }}" method="post">
    <input type="hidden" name="type" value="send" />
    <div class="alert alert-info">{{ text }}</div>
    <fieldset>
       {% for entry in entries %}
            <div class="form-group row">
                <label for="{{ entry.id }}" class="col-sm-4 col-form-label">{{ entry.title }}</label>
                <div class="col-sm-8">
                    {{ entry.input }}
                    {% if entry.descr %}<small id="{{ entry.id }}">{{ entry.descr }}</small>{% endif %}
                    {% if entry.error %}<span class="help-block">{{ entry.error }}</span>{% endif %}
                </div>
            </div>
        {% endfor %}
        <div class="form-group row">
            <div class="col-sm-4"></div>
            <div class="col-sm-8">
                <button type="submit" class="btn btn-success">{{ lang['send_pass'] }}</button>
            </div>
        </div>
    </fieldset>
</form>