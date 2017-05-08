<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=pm">{{ lang['pm'] }}</a></li>
	<li class="active">{{ title }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4>{{ title }}</h4>
		</div>
		<div class="panel-body table-responsive">
			<p>{{ content }}</p>
		</div>
		<div class="panel-footer text-center">
			<a href="admin.php?mod=pm&action=reply&pmid={{ pmid }}" title="{{ lang['reply'] }}" class="btn btn-success edit_form">{{ lang['reply'] }}</a>
		</div>
	</div>
</div>