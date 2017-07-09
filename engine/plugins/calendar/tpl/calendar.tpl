{% if (not flags.ajax) %}
<script>
    function ng_calendar_walk(month, year, offset, category) {
        var url = '{{ admin_url }}/rpc.php';
        var method = 'plugin.calendar.show';
        var params = {'year': year,'offset': offset,'month': month,'category': category,};
        $.reqJSON(url, method, params, function(json) {$('#ngCalendarDiv').html(json.data);});
    }
</script>
{% endif %}
<div id="ngCalendarDiv">
    <div class="widget widget-calendar">
        <h3 class="widget-title">{{ lang['calendar:plugin_title'] }}</h3>
        <table class="table table-sm" id="calendar">
            <thead>
                <tr id="month">
                    <th><a href="{{ prevMonth.link }}" onclick="ng_calendar_walk({{ currentEntry.month }}, {{ currentEntry.year }}, 'prev'); return false;" class="prev-month">«</a></th>
                    <th colspan="5" class="text-center">
                        {% if currentMonth.flags.issetNews %}
                            <a href="{{ currentMonth.link }}">{{ currentMonth.name }} {{ currentEntry.year }}</a>
                        {% else %}
                            {{ currentMonth.name }} {{ currentEntry.year }}
                        {% endif %}
                   </th>
                    <th class="text-right"><a href="{{ nextMonth.link }}" onclick="ng_calendar_walk({{ currentEntry.month }}, {{ currentEntry.year }}, 'next'); return false;" class="next-month">»</a></th>
                </tr>
            </thead>
            <tr class="weeks">
                <td class="weekday"><span>{{ weekdays[1] }}</span></td>
                <td class="weekday"><span>{{ weekdays[2] }}</span></td>
                <td class="weekday"><span>{{ weekdays[3] }}</span></td>
                <td class="weekday"><span>{{ weekdays[4] }}</span></td>
                <td class="weekday"><span>{{ weekdays[5] }}</span></td>
                <td class="weekend"><span>{{ weekdays[6] }}</span></td>
                <td class="weekend"><span>{{ weekdays[7] }}</span></td>
            </tr>
            {% for week in weeks %}
            <tr>
                <td class="{{ week[1].className }}">{% if (week[1].countNews>0) %}<a href="{{ week[1].link }}">{{ week[1].dayNo}}</a>{% else %}<span>{{ week[1].dayNo }}</span>{% endif %}</td>
                <td class="{{ week[2].className }}">{% if (week[2].countNews>0) %}<a href="{{ week[2].link }}">{{ week[2].dayNo}}</a>{% else %}<span>{{ week[2].dayNo }}</span>{% endif %}</td>
                <td class="{{ week[3].className }}">{% if (week[3].countNews>0) %}<a href="{{ week[3].link }}">{{ week[3].dayNo}}</a>{% else %}<span>{{ week[3].dayNo }}</span>{% endif %}</td>
                <td class="{{ week[4].className }}">{% if (week[4].countNews>0) %}<a href="{{ week[4].link }}">{{ week[4].dayNo}}</a>{% else %}<span>{{ week[4].dayNo }}</span>{% endif %}</td>
                <td class="{{ week[5].className }}">{% if (week[5].countNews>0) %}<a href="{{ week[5].link }}">{{ week[5].dayNo}}</a>{% else %}<span>{{ week[5].dayNo }}</span>{% endif %}</td>
                <td class="{{ week[6].className }}">{% if (week[6].countNews>0) %}<a href="{{ week[6].link }}">{{ week[6].dayNo}}</a>{% else %}<span>{{ week[6].dayNo }}</span>{% endif %}</td>
                <td class="{{ week[7].className }}">{% if (week[7].countNews>0) %}<a href="{{ week[7].link }}">{{ week[7].dayNo}}</a>{% else %}<span>{{ week[7].dayNo }}</span>{% endif %}</td>
            </tr>
            {% endfor %}
        </table>
    </div>
</div>