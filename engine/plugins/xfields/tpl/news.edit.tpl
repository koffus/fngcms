{% if (entries) %}<!--br>
    <legend id="xf_profile">{{ lang['xfields:group_title'] }}</legend-->
    {% for entry in entries %}
    <div class="form-group" id="xfl_{{entry.id}}">
        <label class="col-sm-3 control-label">{{entry.title}}{% if entry.flags.required %} <b>(*)</b>{% endif %}</label>
        <div class="col-sm-9">
            {{entry.input}}
        </div>
    </div>
    {% endfor %}
{% endif %}