<div id="list">
<form action="admin.php?mod=extra-config&amp;plugin=cat_description" method="post" name="options_bar">
<input type="hidden" name="action" value="" />
<input type="hidden" name="id" value="0" />
<table width="97%" class="content" border="0" cellspacing="0" cellpadding="0" align="center">
<tr align="center" class="contHead">
<td width="5%">#</td>
<td>{l_cat_description:category}</td>
<td>{l_cat_description:is_on}</td>
<td width="160">{l_cat_description:action}</td>
</tr>
{entries}
<tr><td width="100%" colspan="4">&nbsp;</td></tr>
</table>
</form>
</div>


<div class="well text-center">
    <a href="admin.php?mod=extra-config&plugin=cat_description&action=clearCacheFiles" class="btn btn-primary">{l_btn.clearCacheFiles}</a>
</div>