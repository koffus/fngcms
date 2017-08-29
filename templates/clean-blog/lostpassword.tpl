<!-- Page Header -->
<header class="intro-header" style="background-image: url('{tpl_url}/img/home-bg.jpg')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <div class="post-heading">
                    <h1>{{ lang['lostpassword_title'] }}</h1>
                    <hr class="small">
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Page Content -->
<div class="container">
    <div class="row">
        <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">

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
                        <div class="col-sm-8 offset-sm-4">
                            <button type="submit" class="btn btn-success">{{ lang['send_pass'] }}</button>
                        </div>
                    </div>
                </fieldset>
            </form>

        </div>
    </div>
</div>