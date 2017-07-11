<form method="post" action="">
 <input type="hidden" name="mod" value="extra-config"/>
 <input type="hidden" name="plugin" value="guestbook"/>
 <input type="hidden" name="action" value="save_fields"/>

 <table width="100%" border="0">
 <tr class="contHead" align="left">
 <td width="10%">{{ lang['guestbook:f_id'] }}</td>
 <td width="15%">{{ lang['guestbook:f_name'] }}</td>
 <td width="30%">{{ lang['guestbook:f_placeholder'] }}</td>
 <td width="30%">{{ lang['guestbook:f_default_value'] }}</td>
 <td width="10%">{{ lang['guestbook:f_required'] }}</td>
 <td width="5%" colspan="2">{{ lang['guestbook:actions_title'] }}</td>
 </tr>
 {% for entry in entries %}
 <tr align="left" class="contRow1">
 <td>{{ entry.id }}</td>
 <td>{{ entry.name }}</td>
 <td>{{ entry.placeholder }}</td>
 <td>{{ entry.default_value }}</td>
 <td>{% if entry.required %}{{ lang['guestbook:settings_yes'] }}{% else %}{{ lang['guestbook:settings_no'] }}{% endif %}</td>
 <td nowrap>
 <a href="?mod=extra-config&plugin=guestbook&action=edit_field&id={{ entry.id }}" title="{{ lang['guestbook:actions_edit'] }}">
 <img src="{{ skins_url }}/images/add_edit.png" alt="EDIT" width="12" height="12" />
 </a>
 </td>
 <td nowrap>
 <a onclick="return confirm('{{ lang['guestbook:actions_confirm'] }} {{ entry.id }}?');" href="?mod=extra-config&plugin=guestbook&action=drop_field&id={{ entry.id }}" title="{{ lang['guestbook:actions_drop'] }}">
 <img src="{{ skins_url }}/images/delete.gif" alt="DEL" width="12" height="12" />
 </a>
 </td>
 </tr>
 {% endfor %}
 <tr>
 <td colspan="5" style="text-align: left; padding: 10px 10px 0 0;">
 <a href="?mod=extra-config&plugin=guestbook&action=add_field">Добавить новое поле</a>
 </td>
 </tr>
 </table>
</form>
