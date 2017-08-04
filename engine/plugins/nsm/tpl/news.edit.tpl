<h2 class="section-title">{{ lang.editnews['editnews_title'] }}</h2>
<p><a href="{{ listURL }}">Перейти к списку ваших новостей</a></p>

<form name="form" action="{{ php_self }}" method="post" enctype="multipart/form-data" id="postForm">
	<input type="hidden" name="token" value="{{ token }}" />
	<input type="hidden" name="mod" value="news" />
	<input type="hidden" name="action" value="edit" />
	<input type="hidden" name="subaction" value="submit" />
	<input type="hidden" name="id" value="{{ id }}" />

	<div class="card card-block">
		<div class="form-group row">
			<label for="title" class="col-sm-4 col-form-label">{{ lang.editnews['title'] }}</label>
			<div class="col-sm-8">
				<input type="text" name="title" id="title" value="{{ title }}" class="form-control" />
			</div>
		</div>
		<div class="form-group row">
			<label for="alt_name" class="col-sm-4 col-form-label">{{ lang.editnews['alt_name'] }}</label>
			<div class="col-sm-8">
				<input type="text" name="alt_name" id="alt_name" value="{{ alt_name }}" class="form-control" />
			</div>
		</div>
		<div class="form-group row">
			<label for="alt_name" class="col-sm-4 col-form-label">{{ lang.editnews['category'] }}</label>
			<div class="col-sm-8">
				{{ mastercat }}
			</div>
		</div>
		
		
		{% if flags['multicat.show'] %}
		<div class="form-group row">
			<label for="alt_name" class="col-sm-4 col-form-label">{{ lang['editor.extcat'] }}</label>
			<div class="col-sm-8">
				<div class="btn-group" style="width: 100%;" onclick="$('.cat-list').toggle();">
						<a href="#"id="catSelector" class="btn btn-secondary dropdown-toggle" value="{{ lang['no_cat'] }}" hidefocus="" autocomplete="off" data-toggle="dropdown"style="white-space: pre-wrap;height: auto; text-align: left;" >{{ lang['no_cat'] }}</a>
						<div class="cat-list dropdown-menu card-block " style="display: none; width: 100%;">{{ extcat }}</div>
					</div>
			</div>
		</div>
		{% endif %}
		<div class="form-group row" id="fullwidth">
            <div class="col-sm-12">
				{{ bbcodes }}
				<!-- SMILES -->
				<div id="modal-smiles" class="modal fade" tabindex="-1" role="dialog">
					<div class="modal-dialog modal-sm" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Вставить смайл</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							</div>
							<div class="modal-body text-center smiles">
								{{ smilies }}
							</div>
							<div class="modal-footer">
								<button type="cancel" class="btn btn-default btn-secondary" data-dismiss="modal">{{ lang['cancel'] }}</button>
							</div>
						</div>
					</div>
				</div>
				<tr>
                    <td colspan="2">
                    <b>Текст материала:</b>
                    <div>
                    <div>{{ bbcodes }}<br /> {{ smilies }}<br /><br /></div>
                    <textarea name="ng_news_content" id="ng_news_content" class="textarea">{{ content }}</textarea>
                    </div>
                    </td>
                </tr>
			</div>
		</div>
		{% if not flags['mainpage.disabled'] %}
		<div class="row">
			<label class="col-sm-8 offset-sm-4">
				<input type="checkbox" name="mainpage" value="1" id="mainpage" {% if (flags.mainpage) %}checked="checked" {% endif %}{% if flags['mainpage.disabled'] %}disabled {% endif %} /> {{ lang.editnews['mainpage'] }}
			</label>
		</div>
		{% endif %}
		{% if not flags['pinned.disabled'] %}
		<div class="row">
			<label class="col-sm-8 offset-sm-4">
				<input type="checkbox" name="pinned" value="1" id="pinned" {% if (flags.pinned) %}checked="checked" {% endif %}{% if flags['pinned.disabled'] %}disabled {% endif %} /> {{ lang.editnews['add_pinned'] }}
			</label>
		</div>
		{% endif %}
		{% if not flags['catpinned.disabled'] %}
		<div class="row">
			<label class="col-sm-8 offset-sm-4">
				<input type="checkbox" name="catpinned" value="1" id="catpinned" {% if (flags.catpinned) %}checked="checked" {% endif %}{% if flags['catpinned.disabled'] %}disabled {% endif %} /> {{ lang.editnews['add_catpinned'] }}
			</label>
		</div>
		{% endif %}
		{% if not flags['favorite.disabled'] %}
		<div class="row">
			<label class="col-sm-8 offset-sm-4">
				<input type="checkbox" name="favorite" value="1" id="favorite" {% if (flags.favorite) %}checked="checked" {% endif %}{% if flags['favorite.disabled'] %}disabled {% endif %} /> {{ lang.editnews['add_favorite'] }}
			</label>
		</div>
		{% endif %}
		{% if not flags['html.disabled'] %}
		<div class="row">
			<label class="col-sm-8 offset-sm-4">
				<input name="flag_HTML" type="checkbox" id="flag_HTML" value="1" {% if (flags['html.disabled']) %}disabled {% endif %}{% if flags['html'] %}checked="checked"{% endif %} /> {{ lang.editnews['flag_html'] }}
			</label>
		</div>
		<div class="row">
			<label class="col-sm-8 offset-sm-4">
				<input type="checkbox" name="flag_RAW" value="1" id="flag_RAW" {% if (flags['html.disabled']) %}disabled {% endif %}{% if flags['html'] %}checked="checked"{% endif %} /> {{ lang.editnews['flag_raw'] }}
			</label>
		</div>
		{% endif %}
		{% if flags['params.lost'] %}
			<div class="alert alert-info">
				Обратите снимание - у вас недостаточно прав для полноценного редактирования новости.<br/>
				При сохранении будут произведены следующие изменения:<br/><br/>
				{% if flags['publish.lost'] %}&#8594; Новость будет снята с публикации{% endif %}
				{% if flags['html.lost'] %}&#8594; В новости будет запрещено использование HTML тегов и автоформатирование{% endif %}
				{% if flags['mainpage.lost'] %}&#8594; Новость будет убрана с главной страницы{% endif %}
				{% if flags['pinned.lost'] %}&#8594; С новости будет снято прикрепление на главной{% endif %}
				{% if flags['catpinned.lost'] %}&#8594; С новости будет снято прикрепление в категории{% endif %}
				{% if flags['favorite.lost'] %}&#8594; Новость будет удалена из закладок администратора{% endif %}
				{% if flags['multicat.lost'] %}&#8594; Из новости будут удалены все дополнительные категории{% endif %}
			</div>
		{% endif %}

		<div class="btn-group">

		{% if flags.editable %}
			
			<select name="approve" id="approve" class="form-control">
			{% if flags.can_draft %}<option value="-1" {% if (approve == -1) %}selected="selected"{% endif %}>{{ lang.editnews['state.draft'] }}</option>{% endif %}
			{% if flags.can_unpublish %}<option value="0" {% if (approve == 0) %}selected="selected"{% endif %}>{{ lang.editnews['state.unpublished'] }}</option>{% endif %}
			{% if flags.can_publish %}<option value="1" {% if (approve == 1) %}selected="selected"{% endif %}>{{ lang.editnews['state.published'] }}</option>{% endif %}
			</select>
			
			<input class="btn btn-secondary" type="submit" onClick="return approveMode(-1);" value="Отправить" />
		{% endif %}
		
		<input class="btn btn-secondary" type="button" onClick="preview()" value="Просмотр" />
		
		{% if flags.deleteable %}
		<button class="btn btn-secondary" type="button" onClick="confirmIt('{{ deleteURL }}', '{{ lang['sure_del'] }}')"> <i class="fa fa-trash"></i></button>
		{% endif %}
		</div>
	</div>
</form>

<form name="DATA_tmp_storage" action="" id="DATA_tmp_storage"><input type="hidden" name="area" value="" /></form>

<script type="text/javascript">
function insertselcat(cat) {
	var ss = $('select[name=category] option:selected');
	if( ss.val() == 0 ) {
		$('input[name*=category_]').map(function() {$(this).prop('checked', false);});
	}
	var ctstring = $('input[name*=category_]:checked').map(function() {
		if( $.trim(ss.text()) != $.trim($(this).parent().text()) ) {
			return $(this).parent().text();
		} else {
			$(this).prop('checked', false);
		}
	}).get().join(", ") || "{{ lang['no_cat'] }}";
	$("#catSelector").text( ctstring.replace(/&amp;/g, '&').replace(/^ /g, '') );
}
$('input[name*=category_], select[name=category]').on('click', function (e) {
	insertselcat();
});
insertselcat();

// Global variable: ID of current active input area
var currentInputAreaID = 'ng_news_content_short';

function preview(){
 var form = document.getElementById("postForm");
 if (form.ng_news_content.value == '' || form.title.value == '') {
 alert('{{ lang.nsm['err.preview'] }}');
 return false;
 }

 form['mod'].value = "preview";
 form.target = "_blank";
 form.submit();

 form['mod'].value = "news";
 form.target = "_self";
 return true;
}

function approveMode(mode) {
 document.getElementById('approve').value = mode;
 return true;
}

// Restore variables if needed
var jev = {{ JEV }};
var form = document.getElementById('postForm');
for (i in jev) {
 //try { alert(i+' ('+form[i].type+')'); } catch (err) {;}
 if (typeof(jev[i]) == 'object') {
 for (j in jev[i]) {
 //alert(i+'['+j+'] = '+ jev[i][j]);
 try { form[i+'['+j+']'].value = jev[i][j]; } catch (err) {;}
 }
 } else {
 try {
 if ((form[i].type == 'text')||(form[i].type == 'textarea')||(form[i].type == 'select-one')) {
 form[i].value = jev[i];
 } else if (form[i].type == 'checkbox') {
 form[i].checked = (jev[i]?true:false);
 }
 } catch(err) {;}
 }
}
</script>

<div id="modal-dialog" class="modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>

/* **************** Insert image ****************** */

$(document).on('click', '.preview-img a', function(){
	$(this).html('<span class=text-success>&#10004;</span>');
});

function insert_image(text, area) {
	var form = document.forms['form'];
	try {
	 var xarea = document.forms['DATA_tmp_storage'].area.value;
	 if (xarea != '') area = xarea;
	} catch(err) {;}
	var control = document.getElementById(area);
	// IE
	if (document.selection && document.selection.createRange){
		sel = document.selection.createRange();
		sel.text = text = sel.text;
	} else
	// Mozilla
	if (control.selectionStart || control.selectionStart == "0"){
		var startPos = control.selectionStart;
		var endPos = control.selectionEnd;

		control.value = control.value.substring(0, startPos) + text + control.value.substring(startPos, control.value.length);
		//control.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
	} else {
		control.value += text;
	}
	$('html, body').animate({ scrollTop: $(control).offset().top-200 }, 888);
}

/* Получение списка изображений */
function getImageList(id, npp, page) {
	
	ngShowLoading();
	
	$.post('{{ admin_url }}/admin.php?mod=images&npp=' + npp + '&page=' + page, function (r) {
		var qw = $('.img-src a', r).attr('href');
		if (!qw) {
			showModal('Нет загруженных изображений!',
						'Выберите изображение для вставки',
						'<button type="button" class="btn btn-default btn-secondary pull-left" data-dismiss="modal">\
							<i class="fa fa-times"></i>\
						</button>\
						<button type="button" class="btn btn-primary pull-left" onclick="getImageList(\'img_popup\', 8, 1);return false;">\
							<i class="fa fa-refresh"></i>\
						</button>\
						<a href="#" class="btn btn-primary pull-left" onclick="$(\'#modal-dialog .modal-body\').load(\'{{ admin_url }}/admin.php?mod=images #upload-files\');return false;">\
							<i class="fa fa-upload"></i>\
						</a>');
			ngHideLoading();
			return false;
			
		} else {
			setTimeout(function () {
				var modalContent = '';
				var snum = '0';
				$(".img-src a", r).each(function () {
					snum++;
					var hrf = $(this).attr('href');
					var tr = $(this).closest('tr');
					var title = $('.img-title', tr).text();
					var width = $('.img-width', tr).text();
					var height = $('.img-height', tr).text();
					var size = $('.img-size', tr).text();
					if (size=='-') {
						modalContent += '<div class="preview-img" title="' + title + '"><a href="' + hrf + '" target="_blank">' + title + '</a><div class="img-descr"><span>Wrong Image source!</span></div></div>';
					} else {
						modalContent += '<div class="col-md-3 text-center" ><div class="preview-img" style="background-image: url(\'' + hrf + '\');" title="' + title + '">\
						<span class="img-descr"><span class="img-title">' + title + '</span></span>' +
							$('.insert-file', tr).html().replace('insertimage','insert_image') + 
							$('.insert-thumb', tr).html().replace('insertimage','insert_image') + 
							$('.insert-preview', tr).html().replace('insertimage','insert_image') + 
					'</div></div>';
					}
				});
				
				$("#modal-dialog .modal-dialog").addClass('modal-lg');
				
				$("#modal-dialog").animate({ scrollTop: 0 }, 888);
				
				showModal('<div class="row">' + modalContent + '</div>',
						'Выберите изображение для вставки',
						'<button type="button" class="btn btn-default btn-secondary pull-left" data-dismiss="modal">\
							<i class="fa fa-times"></i>\
						</button>\
						<a href="#" class="btn btn-primary pull-left" onclick="getImageList(\'img_popup\', 8, 1);return false;">\
							<i class="fa fa-refresh"></i>\
						</a>\
						<a href="#" class="btn btn-primary pull-left" onclick="$(\'#modal-dialog .modal-body\').load(\'{{ admin_url }}/admin.php?mod=images #upload-files\');return false;">\
							<i class="fa fa-upload"></i>\
						</a>\
						<button type="button" class="btn btn-success img-back">\
							<i class="fa fa-backward"></i>\
						</button>\
						<button type="button" class="btn btn-success img-next">\
							<i class="fa fa-forward"></i>\
						</button>',
						'modal-lg');
				
				if (page<2) {$('.img-back').attr('disabled','disabled');} else {var page_back = page - 1; $('.img-back').attr('onclick','ngShowLoading();getImageList(\'' + id + '\', ' + npp + ', ' + page_back + ');ngHideLoading(); return false;');}

				if (snum<npp || page==0) {$('.img-next').attr('disabled','disabled');} else {page++; $('.img-next').attr('onclick','ngShowLoading();getImageList(\'' + id + '\', ' + npp + ', ' + page + ');ngHideLoading(); return false;');}
				
			}, 101);
		}
		ngHideLoading();
	});
}

/*
Для input type="file"
HTML
<div class="btn btn-default btn-secondary btn-fileinput">
	<span><i class="fa fa-plus"></i> Add files ...</span>
	<input type="file" name="image" id="image-con" onchange="validateFile(this);">
</div>
*/
function checkImage(where, idnumber) {
	var preview = document.getElementById('preview' + idnumber);
	preview.innerHTML = '';
	[].forEach.call(where.files, function(file) {
		if (file.type.match(/image.*/)) {
			var reader = new FileReader();
			reader.onload = function(event) {
				var img = document.createElement('img');
				img.src = event.target.result;
				img.style.cssText = 'vertical-align: top; width: 88px;';
				preview.appendChild(img);
			};
			reader.readAsDataURL(file);
		}
	});
}

function validateFile(fileInput,multiple,fileMaxSize) {
	var htext = '';
	var hsize = '';
	var btnFileInput = $(fileInput).closest('.btn-fileinput');
	
	if (!fileInput.value) {
		btnFileInput.attr('style', '');
		btnFileInput.addClass('btn');
		btnFileInput.children('span').eq(0).html('<i class="fa fa-plus"></i> Add files ...');
		btnFileInput.children('span').attr('style', '');
		return false;
	}
	
	if (multiple) {
		for (var i=0;i<fileInput.files.length;i++) {
			if (fileMaxSize) {
				htext += '<tr><td style="overflow:hidden;text-overflow:ellipsis;max-width: 400px;">' + fileInput.files[i].name+'</td><td nowrap><b class="pull-right' + (fileInput.files[i].size>fileMaxSize?' text-danger':'') + '">'+formatSize(fileInput.files[i].size)+'</b></td></tr>';
			} else {
				htext += '<tr><td style="overflow:hidden;text-overflow:ellipsis;max-width: 400px;">' + fileInput.files[i].name+'</td><td nowrap><b class="pull-right">'+formatSize(fileInput.files[i].size)+'</td></tr>';
			}
			hsize = Number(fileInput.files[i].size) + Number(hsize);
		}
		
		btnFileInput.removeClass('btn');
		btnFileInput.children('span').eq(0).html('<table\
			class="table-condensed" style="width: 100%;">\
			' + htext + '<tr><td colspan="2" class="text-right">' + formatSize(hsize) + '</td></tr></table><div class="progress"><div id="progressbar" class="progress-bar progress-bar-success" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div>');
		btnFileInput.children('span').eq(0).css({'width': '100%', 'display': 'block'/*, 'white-space': 'nowrap'*/});
		btnFileInput.css({'width': '100%', 'display': 'block'});
	} else {
		for (var i=0;i< fileInput.files.length;i++) {
			htext += fileInput.files[i].name+' ('+formatSize(fileInput.files[i].size)+')<br />';
			hsize = Number(fileInput.files[i].size) + Number(hsize);
		}
		
		btnFileInput.children('span').eq(0).html(htext);
	}
	
	return true;
}

var attachAbsoluteRowID = 0;
function attachAddRow(id) {
	
	++attachAbsoluteRowID;
	var tbl = document.getElementById(id);
	var lastRow = tbl.rows.length;
	var row = tbl.insertRow(lastRow - 1);
	
	// Add cells, Add file input
	if ( id == 'imageup2' || id == 'fileup2' ) {
		row.insertCell(0).innerHTML = '<input type="text" name="userurl[' + attachAbsoluteRowID + ']" class="form-control">'
	} else if ( id == 'imageup' || id == 'fileup' ) {
		row.insertCell(0).innerHTML = '<div class="btn btn-default btn-secondary btn-fileinput">\
							<span><i class="fa fa-plus"></i> Add files ...</span>\
							<input type="file" name="userfile[' + attachAbsoluteRowID + ']" onchange="validateFile(this, multiple);" multiple="multiple" / >\
						</div>';
	} else if ( id == 'attachFilelist' ) {
		row.insertCell(0).innerHTML = '<div class="btn btn-default btn-secondary btn-fileinput">\
								<span><i class="fa fa-plus"></i> Add files ...</span>\
								<input type="file" name="userfile[]" onchange="validateFile(this, multiple);" multiple="multiple" / >\
							</div>';
	} else if ( id == 'attachFilelist_edit' ) {
		var xCell = row.insertCell(0);
		xCell.setAttribute('colspan', '5');
		xCell.innerHTML = '<div class="btn btn-default btn-secondary btn-fileinput">\
							<span><i class="fa fa-plus"></i> Add files ...</span>\
							<input type="file" name="userfile[]" onchange="validateFile(this, multiple);" multiple="multiple" />\
						</div>';
	} else {
		row.insertCell(0).innerHTML = '<div class="btn btn-default btn-secondary btn-fileinput">\
							<span><i class="fa fa-plus"></i> Add files ...</span>\
							<input type="file" name="userfile[' + attachAbsoluteRowID + ']" onchange="validateFile(this);">\
						</div>';
	}
	
	var xCell = row.insertCell(1);
	xCell.setAttribute('class', 'text-center');
	
	el = document.createElement('button');
	el.setAttribute('type', 'button');
	el.setAttribute('onclick', 'document.getElementById("' + id + '").deleteRow(this.parentNode.parentNode.rowIndex);');
	el.setAttribute('class', 'btn btn-danger');
	el.innerHTML = '<i class="fa fa-minus"></i>';
	xCell.appendChild(el);

}

$(document).on('submit', '#upload-files', function(e){
	
	e.preventDefault();
	$("#modal-dialog").animate({ scrollTop: 0 }, 888);
	var progressBar = $('#progressbar');
	
	var $formData = new FormData($(this)[0]);
	$formData.append('ngAuthCookie', '{authcookie}');
	$formData.append('category', document.getElementById('categorySelect').value);
	$formData.append('rand', document.getElementById('flagRand').checked?1:0);
	$formData.append('replace', document.getElementById('flagReplace').checked?1:0);
	
	if ( $("input[name='uploadType']").val() == 'image' ) {
		$formData.append('uploadType', 'image');
		$formData.append('thumb', document.getElementById('flagThumb').checked?1:0);
		$formData.append('stamp', document.getElementById('flagStamp').checked?1:0);
		$formData.append('shadow', document.getElementById('flagShadow').checked?1:0);
	} else {
		$formData.append('uploadType', 'file');
	}
	
	$.each($(this).find("input[type='file']"), function(i, tag) {
		var input, filter, table, tr, td, i;
		table = $(this).parent().find('table');

		$.each($(tag)[0].files, function(i, file) {
			tr = table.find('tr');
			$formData.append('Filedata', file);
			$.ajax({
				url: '{{ admin_url }}/rpc.php?methodName=admin.files.upload',
				data: $formData,
				processData: false,
				contentType: false,
				type: 'POST',
				//dataType: 'JSON',
				xhr: function(){
					var xhr = $.ajaxSettings.xhr();
					xhr.upload.addEventListener('progress', function(evt){
					 if(evt.lengthComputable) {
						var percentComplete = Math.floor(evt.loaded / evt.total * 100);
						progressBar.css('width', percentComplete + '%').text(percentComplete + '%');
					 }
					}, false);
					return xhr;
				},
				success: function (res) {
					// Response should be in JSON format
					var resData;
					var resStatus = 0;
					td = tr[i].getElementsByTagName('td')[0];
					tr[i].style.background = 'white';
					tr[i].style.color = 'black';
					
					try {
						resData = eval(res);
						if (typeof(resData['status']))
							resStatus = 1;
					} catch (err) {
						alert('Error parsing JSON output. Result: '+res);
					}
					if (!resStatus) {
						alert('Upload resp: '+res);
						tr[i].style.color = 'red';
						return false;
					}
					
					flagRequireReload = 1;
					
					// If upload fails
					if (resData['status'] < 1) {
						el = document.createElement('div');
						el.setAttribute('class', 'text-danger');
						el.innerHTML = '('+resData['errorCode']+') '+resData['errorText'];
						td.appendChild(el);
						if (typeof(resData['errorDescription']) !== 'undefined') {
							el = document.createElement('div');
							el.setAttribute('class', 'text-info');
							el.innerHTML = resData['errorDescription'];
							td.appendChild(el);
						}
						tr[i].style.color = 'red';
						return false;
					} else {
						el = document.createElement('div');
						el.setAttribute('class', 'text-success');
						el.innerHTML = resData['errorText'];
						td.appendChild(el);
						//$(tr[i]).fadeOut(3000);
					}
					return true;
				},
				error : function(res) {
					console.log(res.responseText);
					$.notify({message:'Error parsing JSON output.'},{type:'danger'});
					tr[i].style.color = 'red';
					return false;
				}
				
			});
		});
		
	});
});
</script>
<style>
.preview-img {
	height: 150px;overflow:hidden;
	margin-bottom:30px;
	box-shadow: 0 0 0 4px #fff, 0 0 0 5px #ccc;
	-webkit-background-size: cover;
	-moz-background-size: cover;
	background-size: cover;
	background-repeat: no-repeat;
	background-position: 50% 28px;
	-webkit-backface-visibility: hidden;
	-moz-backface-visibility: hidden;
	-ms-backface-visibility: hidden;
	backface-visibility: hidden;
	-webkit-transform: scale(0.99999);
	-moz-transform: scale(0.99999);
	-o-transform: scale(0.99999);
	-ms-transform: scale(0.99999);
	transform: scale(0.99999);
	white-space:nowrap;
	-webkit-animation: hue 60s infinite linear;
}

.preview-img .img-descr {
	position: absolute;
	bottom: 0;
	right: 0;
	left: 0;
	padding: 0 8px 8px;
	color: #fff;
	background: -webkit-linear-gradient(rgba(0,0,0,0.3) 0%,rgba(0,0,0,0.8) 100%);
	background: -moz-linear-gradient(rgba(0,0,0,0.3) 0%,rgba(0,0,0,0.8) 100%);
	background: -o-linear-gradient(rgba(0,0,0,0.3) 0%,rgba(0,0,0,0.8) 100%);
	background: -ms-linear-gradient(rgba(0,0,0,0.3) 0%,rgba(0,0,0,0.8) 100%);
	background: linear-gradient(rgba(0,0,0,0.3) 0%,rgba(0,0,0,0.8) 100%);
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#4d000000',endColorstr='#cc000000',GradientType=0);
}
.preview-img .img-descr:before {
	content: '';
	position: absolute;
	left: 0;
	top: -15px;
	height: 15px;
	width: 100%;
	background: -webkit-linear-gradient(rgba(0,0,0,0) 0%,rgba(0,0,0,0.3) 100%);
	background: -moz-linear-gradient(rgba(0,0,0,0) 0%,rgba(0,0,0,0.3) 100%);
	background: -o-linear-gradient(rgba(0,0,0,0) 0%,rgba(0,0,0,0.3) 100%);
	background: -ms-linear-gradient(rgba(0,0,0,0) 0%,rgba(0,0,0,0.3) 100%);
	background: linear-gradient(rgba(0,0,0,0) 0%,rgba(0,0,0,0.3) 100%);
}
.preview-img .img-title {
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
	display: inherit;
}
.cat-list {
	overflow: auto;
	max-height: 150px;
}
/*
 * BBCODES && SMILES
*/

.bbcodes .btn {
	border: 0;
	font-size: 1em;
	padding: 8px 1px;
}
.bbcodes {
	border: 1px solid #ccc;
	border-radius: 4px 4px 0 0;
	border-bottom: none;
}
.bbcodes .btn:last-child:not(:first-child) {
	border-bottom-right-radius: 0;
}
.bbcodes .btn:first-child {
	border-bottom-left-radius: 0;
}
.smiles .btn {
	border: 1px solid transparent;
	background: none
	}
.smiles .btn:hover {
	border: 1px solid #e7e7e7;
	}
.smiles .btn {
 margin: 1px;
	}
/* SPOILERs */
.spoiler {
 margin: 10px 0;
 padding: 0px;
 clear: both;
}
.sp-head {
 border: 1px solid #ccc;
 background: #eee;
 padding: 2px 4px;
 cursor: pointer;
 color: #888;
}
.spoiler .sp-head b {}
.spoiler .sp-head b.expanded {}
.sp-body {
 display: none;
 border: 1px solid #ccc;
 border-top: 0;
 padding: 4px 8px;
}
/*
 * Checkbox and radio
 */
input[type=radio],input[type=checkbox]{font-weight:700;border:1px solid #bbb;color:#555;cursor:pointer;display:inline-block;line-height:0;height:15px;width:15px;margin:0;outline:0!important;padding:0!important;text-align:center;vertical-align:middle;-webkit-appearance:none;}input[type=radio]{border-radius:50%;line-height:15px;}input[type=radio]:checked:before,input[type=checkbox]:checked:before{float:left;display:inline-block;vertical-align:middle;width:15px;font-size:15px;}input[type=checkbox]:checked:before{content:'\2714';margin:6px 0 0 0;color:#08c;}input[type=radio]:checked:before{content:'\2022';text-indent:-9999px;border-radius:50px;font-size:22px;width:7px;height:7px;margin:3px;line-height:15px;background-color:#08c}.checkbox input[type=checkbox],.checkbox-inline input[type=checkbox],.radio input[type=radio],.radio-inline input[type=radio]{position:relative;}label>input[type=radio],label>input[type=checkbox]{margin-right:8px}input[type="checkbox"]:disabled,input[type="radio"]:disabled {border:1px dashed #ccc}input[type="checkbox"]:disabled:before,input[type="radio"]:disabled:before {color:#ccc}
/* 
 * Input[type="file"]
 */
.btn-fileinput{cursor:pointer;display:inline-block;overflow:hidden;position:relative;height:auto;text-align:left}.btn-fileinput input[type=file]{margin:0;cursor:pointer;font-size:100px;filter:alpha(opacity=1);-moz-opacity:0.01;opacity:0.01;position:absolute;right:0;top:0}.btn-fileinput span{cursor:pointer;display:inline-block;white-space:normal}.btn-fileinput .table{margin:0;}

</style>