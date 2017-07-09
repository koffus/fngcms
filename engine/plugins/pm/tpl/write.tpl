<h2 class="section-title">{{ lang['pm:new'] }}</h2>

<form method="post" name="form" action="{{ php_self }}?action=send">

	<div class="row">
		<div class="col-md-4">
			<div class="list-group">
				<a href="{{ php_self }}?action=write" class="list-group-item list-group-item-action active">{{ lang['pm:write'] }}</a>
				<a href="{{ home }}/plugin/pm/" class="list-group-item list-group-item-action"><i class="fa fa-inbox"></i>&nbsp;{{ lang['pm:inbox'] }}</a>
				<a href="{{ home }}/plugin/pm/?action=outbox" class="list-group-item list-group-item-action"><i class="fa fa-envelope-o"></i>&nbsp;{{ lang['pm:outbox'] }}</a>
				<a href="{{ php_self }}?action=set" class="list-group-item list-group-item-action"> <i class="fa fa-cog"></i>&nbsp;{{ lang['pm:set'] }}</a>
			</div>
		</div>

		<div class="col-md-8">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<input type="text" name="title" tabindex="2" placeholder="{{ lang['pm:subject'] }}" class="form-control" />
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<input type="text" name="to_username" id="to_username" tabindex="3" autocomplete="off" value="{{ username }}" placeholder="{{ lang['pm:too'] }}" class="form-control" />
						<span id="suggestLoader" style="width: 20px;visibility: hidden;position: absolute;top: 8px;right: 15px;"><i class="fa fa-spinner fa-pulse"></i></span>
					</div>
				</div>
			</div>
			<div class="form-group">
				{{ bbcodes }}
				<!-- SMILES -->
				<div id="modal-smiles" class="modal fade" tabindex="-1" role="dialog">
					<div class="modal-dialog modal-sm" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Вставить смайл</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							</div>
							<div class="modal-body text-center">
								{{ smilies }}
							</div>
							<div class="modal-footer">
								<button type="cancel" class="btn btn-secondary" data-dismiss="modal">{l_cancel}</button>
							</div>
						</div>
					</div>
				</div>
				<textarea name="content" id="pm_content" rows="8" class="form-control message-content" /></textarea>
			</div>
			<div class="form-group">
				<input name="saveoutbox" type="checkbox"/> {{ lang['pm:saveoutbox'] }}
			</div>
			<div class="form-group">
				<input type="submit" value="{{ lang['pm:send'] }}" accesskey="s" class="btn btn-success" />
			</div>
		</div>
	</div>
</form>

<script src="{{ scriptLibrary }}/libsuggest.js"></script>

<script type="text/javascript">
	function systemInit() {
		new ngSuggest('to_username',
			{
				'stCols' : 1,
				'stColsClass': ['cleft'],
				'lId' : 'suggestLoader',
				'hlr' : 'true',
				'stColsHLR'	: [ true ],
				'reqMethodName' : 'pm_get_username',
				'localPrefix' : '{{ home }}',
			}
		);
	}
	if (document.body.attachEvent) {
		document.body.onload = systemInit;
	} else {
		systemInit();
	}
</script>