<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=pm">{{ lang['pm'] }}</a></li>
	<li class="active">{{ lang['write'] }}</li>
</ul>

<!-- Info content -->
<div class="page-main">
	<div id="add_edit_form">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form name="form" action="admin.php?mod=pm" method="post" class="form-horizontal">
					<input type="hidden" name="action" value="send">
					<input type="hidden" name="title" value="{{ title }}">
					<input type="hidden" name="sendto" value="{{ sendto }}">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4>{{ lang['write'] }}</h4>
					</div>
					<div class="modal-body">
						{{ quicktags }}
						<div class="btn-group btn-group-justified smiles" data-toggle="buttons">
							{{ smilies }}
						</div>
						<textarea name="content" id="content" rows="10" tabindex="1" maxlength="3000" class="form-control"></textarea>
					</div>
					<div class="modal-footer">
						<button type="cancel" class="btn btn-default" data-dismiss="modal">{{ lang['cancel'] }}</button>
						<button type="submit" class="btn btn-success">{{ lang['send'] }}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
