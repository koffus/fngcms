<ul class="list-unstyled">
{% for entry in entries %}
    <!-- Если не стоит флаг `flags.active`, т.е. если эта категория - не текущая, то показываем ссылку -->
    <!-- В текущей категории показываем имя категории жирным шрифтом -->
    <li class="list-unstyled">
    {% if (not entry.flags.active) %}
        <a href="{{ entry.link }}" class="dropdown-item">
    {% else %}
        <b>
    {% endif %}
    {{ entry.cat }}
 
    <!-- Отображаем кол-во новостей в категории только в случае, если выставлен флаг `flags.counter` -->
    {% if (entry.flags.counter) %}
        [{{ entry.counter }}]
    {% endif %}
 
    {% if (not entry.flags.active) %}
        </a>
    {% else %}
        </b>
    {% endif %}
 
    <!-- Если у категории есть подкатегории, то открываем новый уровень вложенности -->
    {% if (entry.flags.hasChildren) %}
        <ul>
    {% else %}
    </li>
        <!-- Если после этой категории закрывается 1 или несколько уровней - выводим закрывающиеся </ul> -->
        {% if isSet(entry.closeToLevel) %}
            {% for i in (entry.closeToLevel+1) .. entry.level %}
                </ul></li>
            {% endfor %}
        {% endif %}
    {% endif %}
{% endfor %}
</ul>