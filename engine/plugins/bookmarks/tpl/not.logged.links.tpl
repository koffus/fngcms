<span id="bookmarks_{{news}}">
    <a href="#" class="dropdown-item" onclick="return false;">
        <i class="fa fa-bookmark{% if not(found) %}-o{% endif %}"></i> {% if (counter > 0) %}В закладках у {{ counter }} пользователей{% else %}Не добавляли в закладки{% endif %}
   </a>
</span>