<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang.home }}</a></li>
	<li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
	<li><a href="admin.php?extra-config&plugin=ads_pro" title="ads_pro">ads_pro</a></li>
	<li class="active">{{ action }}</li>
</ul>

<!-- Info content -->
<div class="page-main">

		<ul class="nav nav-tabs nav-justified">
			<li class="active"><a href="admin.php?mod=extra-config&plugin=ads_pro">{{ lang['ads_pro:button_general'] }}</a></li>
			<li class="{{ class['grp.news'] }}"><a href="admin.php?mod=extra-config&plugin=ads_pro&action=list">{{ lang['ads_pro:button_list'] }}</a></li>
			<li class="{{ class['tdata'] }}"><a href="admin.php?mod=extra-config&plugin=ads_pro&action=add">{{ lang['ads_pro:button_add'] }}</a></li>
		</ul>

		{{ entries }}

</div>
