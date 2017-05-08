<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li class="active">{{ lang['pm'] }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<div class="panel panel-default panel-table">
		<div class="panel-heading text-right">
			<a href="admin.php?mod=pm&action=write" title="{{ lang['write'] }}" class="btn btn-success add_form"><i class="fa fa-plus"></i></a>
		</div>
		<form name="form" action="admin.php?mod=pm" method="post">
			<div class="panel-body table-responsive">
				<table class="table table-condensed">
					<thead>
						<tr>
							<th><input type="checkbox" class="select-all"></th>
							<th>{{ lang['from'] }}</th>
							<th>{{ lang['title'] }}</th>
							<th>{{ lang['status'] }}</th>
							<th>{{ lang['pmdate'] }}</th>
							<th class="text-right">{{ lang['action'] }}</th>
						</tr>
					</thead>
					<tbody>
						{{ entries }}
					</tbody>
				</table>
			</div>
			<div class="panel-footer text-right">
				<div class="row">
					<div class="col col-md-4">
						<div class="input-group">
							<select name="action" class="form-control">
								<option value="">-- {{ lang['action'] }} --</option>
								<option value="delete">{{ lang['delete'] }}</option>
							</select>
							<span class="input-group-btn">
								<button type="submit" class="btn btn-default">{{ lang['ok'] }}</button>
							</span>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
