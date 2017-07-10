<a href="#" class="dropdown-item" onclick="bookmarks('{{news}}', '{{action}}'); return false;">
    <i class="fa fa-bookmark{% if (action == 'add') %}-o{% endif %}"></i> {{link_title}} {{counter}}
</a>
