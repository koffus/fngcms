<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang.home }}</a></li>
	<li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
	<li><a href="admin.php?mod=extra-config&plugin=ads_pro" title="ads_pro">ads_pro</a></li>
	<li class="active">{{ action }}</li>
</ul>

<!-- Info content -->
<div class="page-main">

		<ul class="nav nav-tabs">
			<li class="{{ class['general'] }}"><a href="admin.php?mod=extra-config&plugin=ads_pro">{{ lang['ads_pro:btn.general'] }}</a></li>
			<li class="{{ class['list'] }}"><a href="admin.php?mod=extra-config&plugin=ads_pro&action=list">{{ lang['ads_pro:btn.list'] }}</a></li>
			<li class="{{ class['add_edit'] }}">
				{% if (flags.edit) %}
					<a href="admin.php?mod=extra-config&plugin=ads_pro&action=edit&id={{ id }}">{{ lang['ads_pro:btn.edit'] }}</a>
				{% else %}
					<a href="admin.php?mod=extra-config&plugin=ads_pro&action=add">{{ lang['ads_pro:btn.add'] }}</a>
				{% endif %}
			</li>
		</ul>

		<br />
		{{ entries }}

</div>
