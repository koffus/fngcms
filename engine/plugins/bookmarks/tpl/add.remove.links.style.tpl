{% if (global.flags.isLogged) %}
<span id="bookmarks_{{news}}">
    <a href="#" class="dropdown-item" onclick="bookmarks('{{news}}', '{{action}}'); return false;">
        <i class="fa fa-bookmark{% if not(found) %}-o{% endif %}"></i> {{link_title}} {{counter}}
   </a>
</span>
<script>
function bookmarks(news, action) {
    var params = {'news': news, 'action': action};
    $.reqJSON('{{ admin_url }}/rpc.php', 'plugin.bookmarks.update', params, function(json) {
        elementObj = document.getElementById("bookmarks_" + news);
        elementObj.innerHTML = json.content;
        elementObj = document.getElementById("bookmarks_counter_" + news);
        if(action == 'add'){
            $.notify({message: "{{ lang['bookmarks:msg_add'] }}" },{type: 'info'});
        } else {
            $.notify({message: "{{ lang['bookmarks:msg_delete'] }}" },{type: 'info'});
        }
    });
}
</script>
{% endif %}