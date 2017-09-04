<h2 class="section-title">{{ lang['login.title'] }}</h2>

<div class="row">
    <div class="col-md-8">

        <form name="login" method="post" action="{{ form_action }}">
            <input type="hidden" name="redirect" value="{{ redirect }}"/>
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
                        <button type="submit" class="btn btn-success">{{ lang['login.submit'] }}</button>
                    </div>
                </div>
            </fieldset>
        </form>

    </div>
</div>