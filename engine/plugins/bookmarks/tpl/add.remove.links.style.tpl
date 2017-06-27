<span id="bookmarks_{{news}}">
    <a href="{{link}}" class="dropdown-item">
        {% if (found) %}<img src="{{ home }}/engine/plugins/bookmarks/img/delete.gif" />
        {% else %}<img src="{{ home }}/engine/plugins/bookmarks/img/add.gif" />{% endif %}
        {{link_title}}
   </a> {{counter}}
</span>
<script>
	var el = document.getElementById('bookmarks_{{news}}').getElementsByTagName('a')[0];
	el.setAttribute('href', '#');
	el.setAttribute('onclick', 'bookmarks("{{url}}","{{news}}","{{action}}"); return false;');
</script>