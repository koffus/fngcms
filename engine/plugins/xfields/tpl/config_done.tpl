<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=extras">{{ lang['extras'] }}</a></li>
	<li><a href="admin.php?mod=extra-config&plugin=xfields&section={{ sectionID }}">{{ lang.xfconfig['config_text'] }} xfields</a></li>
	<li class="active">{{ lang.xfconfig['editfield'] }} xfields <a href="admin.php?mod=extra-config&plugin=xfields&action=edit&section={{ sectionID }}&field={{ id }}">{{ id }}</a></li>
</ul>

<!-- Info content -->
<div class="page-main">
	<div class="alert alert-info">
		{{ lang.xfconfig['savedone'] }}
	</div>
</div>