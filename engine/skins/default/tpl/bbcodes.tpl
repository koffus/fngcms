<span id="save_area" style="display: block;"></span>
<div id="tags" class="btn-group btn-group-justified bbcodes" data-toggle="buttons">

	<a href="#" class="btn btn-sm btn-secondary btn-default" onclick="insertext('[p]','[/p]', {area})" title='{l_bb.paragraph}'><i class="fa fa-paragraph"></i></a>

	<div class="btn-group">
		<button type="button" class="btn btn-sm btn-secondary btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-font"></i> <span class="caret"></span></button>
		<ul class="dropdown-menu">
			<li class="nav-item"><a href="#" onclick="insertext('[b]','[/b]', {area})" class="dropdown-item"><i class="fa fa-bold"></i> {l_bb.bold}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[u]','[/u]', {area})" class="dropdown-item"><i class="fa fa-underline"></i> {l_bb.underline}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[i]','[/i]', {area})" class="dropdown-item"><i class="fa fa-italic"></i> {l_bb.italic}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[s]','[/s]', {area})" class="dropdown-item"><i class="fa fa-strikethrough"></i>{l_bb.crossline}</a></li>
		</ul>
	</div>
	
	<div class="btn-group">
		<button type="button" class="btn btn-sm btn-secondary btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-align-left"></i> <span class="caret"></span></button>
		<ul class="dropdown-menu">
			<li class="nav-item"><a href="#" onclick="insertext('[left]','[/left]', {area})" class="dropdown-item"><i class="fa fa-align-left"></i> {l_bb.left}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[center]','[/center]', {area})" class="dropdown-item"><i class="fa fa-align-center"></i> {l_bb.center}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[right]','[/right]', {area})" class="dropdown-item"><i class="fa fa-align-right"></i> {l_bb.right}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[justify]','[/justify]', {area})" class="dropdown-item"><i class="fa fa-align-justify"></i> {l_bb.justify}</a></li>
		</ul>
	</div>
	
	<div class="btn-group">
		<button type="button" class="btn btn-sm btn-secondary btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-quote-left"></i> <span class="caret"></span></button>
		<ul class="dropdown-menu">
			<li class="nav-item"><a href="#" onclick="insertext('[ul]\n[li][/li]\n[li][/li]\n[li][/li]\n[/ul]','', {area})" class="dropdown-item"><i class="fa fa-list-ul"></i> {l_bb.bulllist}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[ol]\n[li][/li]\n[li][/li]\n[li][/li]\n[/ol]','', {area})" class="dropdown-item"><i class="fa fa-list-ol"></i> {l_bb.numlist}</a></li>
			<li role="separator" class="dropdown-divider"></li>
			<li class="nav-item"><a href="#" onclick="insertext('[quote]','[/quote]', {area})" class="dropdown-item"><i class="fa fa-quote-left"></i> {l_bb.comment}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[code]','[/code]', {area})" class="dropdown-item"><i class="fa fa-code"></i> {l_bb.code}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[hide]','[/hide]', {area})" class="dropdown-item"><i class="fa fa-shield"></i> {l_bb.hide}</a></li>
			<li class="nav-item"><a href="#" onclick="insertext('[spoiler]','[/spoiler]', {area})" class="dropdown-item"><i class="fa fa-list-alt"></i> {l_bb.spoiler}</a></li>
		</ul>
	</div>
	
	<a href="#modal-url" class="btn btn-sm btn-secondary btn-default" data-toggle="modal" title="{l_bb.link}"><i class="fa fa-link"></i></a>

	[perm]
		<a href="#" class="btn btn-sm btn-secondary btn-default" onclick="try{document.forms['DATA_tmp_storage'].area.value={area};} catch(err){;} getImageList('img_popup', 8, 1);" title='{l_bb.image}'><i class="fa fa-file-image-o"></i></a>
		<a href="#" class="btn btn-sm btn-secondary btn-default" onclick="try{document.forms['DATA_tmp_storage'].area.value={area};} catch(err){;} window.open('admin.php?mod=files&amp;ifield='+{area}, '_Addfile', 'height=600,resizable=yes,scrollbars=yes,width=767');return false;" target="DATA_Addfile" title='{l_bb.file}'><i class="fa fa-file-text-o"></i></a>
		[news]
		<a href="#" class="btn btn-sm btn-secondary btn-default" onclick="insertext('<!--nextpage-->','', {area})" title="{l_bb.nextpage}"><i class="fa fa-files-o"></i></a>
		<a href="#" class="btn btn-sm btn-secondary btn-default" onclick="insertext('<!--more-->','', {area})" title="{l_bb.more}"><i class="fa fa-ellipsis-h"></i></a>
		[/news]
	[/perm]

	<a href="#" class="btn btn-sm btn-secondary btn-default" onclick="insertext('[color=]','[/color]', {area})" title='{l_bb.color}'><i class="fa fa-paint-brush"></i></a>
	<a href="#modal-smiles" class="btn btn-sm btn-secondary btn-default" data-toggle="modal"><i class="fa fa-smile-o"></i></a>
	
</div>

<!-- URL LINK -->
<div id="modal-url" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4>{l_bb.link}</h4>
			</div>

			<div class="modal-body">
				<ul class="nav nav-tabs nav-justified">
					<li class="active"><a href="#tags-link" data-toggle="tab" aria-expanded="true">{l_bb.link}</a></li>
					<li class="nav-item"><a href="#tags-email" data-toggle="tab" aria-expanded="false">{l_bb.email}</a></li>
					<li class="nav-item"><a href="#tags-img-url" data-toggle="tab" aria-expanded="false">{l_bb.imagelink}</a></li>
				</ul>
				
				<div class="form-group"></div>
				
				<!-- Tab panes -->
				<div class="tab-content">
					<!-- LINK -->
					<div id="tags-link" class="tab-pane active">
						<div class="form-group">
							<label class="col-sm-3 control-label">Адрес ссылки</label>
							<div class="col-sm-9">
								<input type="url" id="modal-url-1" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Текст ссылки</label>
							<div class="col-sm-9">
								<input type="text" id="modal-url-2" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"></label>
							<div class="col-sm-9">
								<label><input type="checkbox" id="modal-url-3" /> Open link in new window</label>
							</div>
						</div>
					</div>
					
					<!-- EMAIL -->
					<div id="tags-email" class="tab-pane">
						<div class="form-group">
							<label class="col-sm-3 control-label">Электронная почта</label>
							<div class="col-sm-9">
								<input type="email" id="modal-email-1" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Текст ссылки</label>
							<div class="col-sm-9">
								<input type="text" id="modal-email-2" class="form-control" />
							</div>
						</div>
					</div>
					
					<!-- IMG LINK -->
					<div id="tags-img-url" class="tab-pane">
						<div class="form-group">
							<label class="col-sm-3 control-label">Ссылка на изображение</label>
							<div class="col-sm-9">
								<input type="url" id="modal-img-url-1" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Alternate Text</label>
							<div class="col-sm-9">
								<input type="text" id="modal-img-url-2" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Dimensions</label>
							<div class="col-sm-9">
								<div class="input-group">
									<input type="text" id="modal-img-url-3" placeholder="width" class="form-control">
									<span class="input-group-addon"> x </span>
									<input type="text" id="modal-img-url-4" placeholder="height" class="form-control">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Alignment</label>
							<div class="col-sm-9">
								<select id="modal-img-url-5" class="form-control">
									<option value="0" selected>{l_noa}</option>
									<option value="left">{l_bb.left}</option>
									<option value="center">{l_bb.center}</option>
									<option value="right">{l_bb.right}</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Class</label>
							<div class="col-sm-9">
								<input type="text" id="modal-img-url-6" placeholder="thumbnail" class="form-control" />
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="cancel" class="btn btn-secondary btn-default" data-dismiss="modal">{l_cancel}</button>
				<button type="button" id="modal-url-submit" class="btn btn-success" data-dismiss="modal">{l_ok}</button>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function(){
	$('#modal-url-submit').click(function() {
		var activeTab = $(this).parents('#modal-url').find('.tab-pane.active').prop('id');
		
		if ( activeTab == 'tags-link' ){
			var targetLink = $('#modal-url-3').prop('checked') ? ' target="_blank"' : '';
			insertext('[url=' + $('#modal-url-1').val() + ' ' + targetLink + ']' + $('#modal-url-2').val(),'[/url]', {area})
		}
		
		if ( activeTab == 'tags-email' ){
			insertext('[email=' + $('#modal-email-1').val() + ']' + $('#modal-email-2').val(),'[/email]', {area})
		}
		
		if ( activeTab == 'tags-img-url' ){
			var widthImg = $('#modal-img-url-3').val() ? ' width="' + $('#modal-img-url-3').val() + '"' : '';
			var heightImg = $('#modal-img-url-4').val() ? ' height="' + $('#modal-img-url-4').val() + '"' : '';
			var alignImg = $('#modal-img-url-5').val() !== '0' ? ' align="' + $('#modal-img-url-5').val() + '"' : '';
			var classImg = $('#modal-img-url-6').val() ? ' class="' + $('#modal-img-url-6').val() + '"' : '';
			insertext('[img=' + $('#modal-img-url-1').val() + widthImg + heightImg + alignImg + classImg + ']' + $('#modal-img-url-2').val(),'[/img]', {area})
		}
	});
});
</script>