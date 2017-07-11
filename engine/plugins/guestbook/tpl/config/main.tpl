<style>
.navbutton {
 text-decoration: none;
}
</style>
<div style="text-align : left;">
 <table class="content" border="0" cellspacing="0" cellpadding="0" align="center">
 <tr>
 <td width="100%" colspan="2" class="contentHead">
 <a href="admin.php?mod=extras" title="{{ lang['guestbook:edit_extras'] }}">{{ lang['guestbook:edit_extras'] }}</a> &#8594;
 <a href="{{admin_url}}/admin.php?mod=extra-config&plugin=guestbook">{{ lang['guestbook:guestbook'] }}</a>
 </td>
 </tr>
 </table>

 <table border="0" cellspacing="0" cellpadding="0" width="100%">
 <tr align="center">
 <td width="100%" class="contentNav" align="center" style="background-repeat: no-repeat; background-position: left;">
 <a href="{{admin_url}}/admin.php?mod=extra-config&plugin=guestbook" class="navbutton">{{ lang['guestbook:menu_settings'] }}</a>
 <a href="{{admin_url}}/admin.php?mod=extra-config&plugin=guestbook&action=show_messages" class="navbutton">{{ lang['guestbook:menu_messages'] }}</a>
 <a href="{{admin_url}}/admin.php?mod=extra-config&plugin=guestbook&action=manage_fields" class="navbutton">{{ lang['guestbook:menu_fields'] }}</a>
 <a href="{{admin_url}}/admin.php?mod=extra-config&plugin=guestbook&action=social" class="navbutton">{{ lang['guestbook:menu_social'] }}</a>
 </td>
 </tr>
 </table>

{{ entries }}

</div>
