<script type="text/javascript">
var cajax = new sack();
function reload_captcha() {
	var captc = document.getElementById('img_captcha');
	if (captc != null) {
		captc.src = "{captcha_url}?rand="+Math.random();
	}
}	

function add_comment(){
	// First - delete previous error message
	var perr;
	if (perr=document.getElementById('error_message')) {
		perr.parentNode.removeChild(perr);
	}

	// Now let's call AJAX comments add
	var form = document.getElementById('comment');
	//cajax.whattodo = 'append';
	cajax.onShow("");[not-logged]
	cajax.setVar("name", form.name.value);
	cajax.setVar("mail", form.mail.value);[captcha]
	cajax.setVar("vcode", form.vcode.value); [/captcha][/not-logged]
	cajax.setVar("content", form.content.value);
	cajax.setVar("newsid", form.newsid.value);
	cajax.setVar("ajax", "1");
	cajax.setVar("json", "1");
	cajax.requestFile = "{post_url}"; //+Math.random();
	cajax.method = 'POST';
	//cajax.element = 'new_comments';
	cajax.onComplete = function() { 
		if (cajax.responseStatus[0] == 200) {
			try {
				var resRX = eval('('+cajax.response+')');
				var nc;
				if (resRX['rev'] && document.getElementById('new_comments_rev')) {
					nc = document.getElementById('new_comments_rev');
				} else {
					nc = document.getElementById('new_comments');
				}
				nc.innerHTML += resRX['data'];				
				if (resRX['status']) { 
					// Added successfully!
					form.content.value = '';
				}
 			} catch (err) { 
				alert('Error parsing JSON output. Result: '+cajax.response); 
			}
		} else {
			alert('TX.fail: HTTP code '+cajax.responseStatus[0]);
		}	
		[captcha] 
		reload_captcha();[/captcha]
	}
	cajax.runAJAX();
}
</script>

<h4 class="section-heading">Добавить комментарий</h4>

<div class="respond">
	<form id="comment" method="post" action="{post_url}" name="form" [ajax]onsubmit="add_comment(); return false;"[/ajax] novalidate>
	<input type="hidden" name="newsid" value="{newsid}" />
	<input type="hidden" name="referer" value="{request_uri}" />
		[not-logged]
		<div class="row control-group">
			<div class="form-group col col-xs-12 floating-label-form-group controls">
				<label>Name</label>
				<input type="text" name="name" value="{savedname}" class="form-control" placeholder="Name" id="name" required="" data-validation-required-message="Please enter your name." aria-invalid="false">
				<p class="help-block text-danger"></p>
			</div>
		</div>
		<div class="row control-group">
			<div class="form-group col col-xs-12 floating-label-form-group controls">
				<label>Email Address</label>
				<input type="email" name="mail" value="{savedmail}" class="form-control" placeholder="Email Address" id="email" required="" data-validation-required-message="Please enter your email address." aria-invalid="false">
				<p class="help-block text-danger"></p>
			</div>
		</div>
		[/not-logged]
		{bbcodes}
		<!-- SMILES -->
		<div id="modal-smiles" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-sm" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Вставить смайл</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body text-center">

								{smilies}

						
					</div>
					<div class="modal-footer">
						<button type="cancel" class="btn btn-default" data-dismiss="modal">{l_cancel}</button>
					</div>

				</div>
			</div>
		</div>
		<div class="row control-group">
			<div class="form-group col col-xs-12 floating-label-form-group controls">
				<label>Message</label>
				<textarea onkeypress="if(event.keyCode==10 || (event.ctrlKey && event.keyCode==13)) {add_comment();}" name="content" id="content" rows="5" class="form-control" placeholder="Message" required="" data-validation-required-message="Please enter a message." aria-invalid="false"></textarea>
				<p class="help-block text-danger"></p>
			</div>
		</div>
		[captcha]
		<div class="row control-group">
			<div class="form-group col col-xs-12 floating-label-form-group controls">
				<label>Код безопасности</label>
				<img id="img_captcha" onclick="reload_captcha();" src="{captcha_url}?rand={rand}" alt="captcha" />
				<input type="text" name="vcode" class="form-control" placeholder="Код безопасности" id="captcha" required="" data-validation-required-message="Please enter Код безопасности." aria-invalid="false">
				<p class="help-block text-danger"></p>
			</div>
		</div>
		[/captcha]
		<div id="new_comments"></div>
		<br />
		<div class="row">
			<div class="form-group col col-xs-12">
				<button type="submit" id="sendComment" class="btn btn-default">Добавить комментарий</button>
			</div>
		</div>
	</form>
</div>