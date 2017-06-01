<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{l_home}</a></li>
	<li><a href="admin.php?mod=extras">{l_extras}</a></li>
	<li class="active">{mode_text}</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<form action="admin.php?mod=extras" method="post">
		<input type="hidden" name="token" value="{token}" />
		<input type="hidden" name="enable" value="{plugin}" />
		
		<div class="panel panel-default panel-table">
			<div class="panel-heading">
				<h3>{plugin}</h3>
			</div>
			<div class="panel-body">
				<table class="table table-condensed">
					{entries}
				</table>
			</div>
			<div class="panel-footer">
				<h4>{msg}</h4>
			</div>
		</div>
		[enable]
		<div class="well text-center">
			<button type="submit" class="btn btn-success">Включить плагин</button>
		</div>
		[/enable]
	</form>
</div>
