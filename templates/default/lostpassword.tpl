[TWIG]
<h2 class="section-title">{{ lang['lostpassword_title'] }}</h2>

<form name="lostpassword" action="{{ form_action }}" method="post">
	<input type="hidden" name="type" value="send" />

	{{ entries }}
	[captcha]
	<div class="form-group row">
		<label for="vcode" class="col-sm-4 col-form-label">{{lang.theme['captcha_title'] }}</label>
		<div class="col-sm-8">
			<div class="input-group">
				<input type="text" name="vcode" id="vcode" class="form-control" required />
				<span class="input-group-addon p-0">
					<img src="{{ admin_url }}/captcha.php" id="img_captcha" onclick="reload_captcha();" alt="Security code" class="captcha"/>
				</span>
			</div>
			<small id="{{ entry.id }}">{{ lang['captcha_desc'] }}</small>
		</div>
	</div>
	[/captcha]
	<div class="form-group">
		<div class="col-sm-8 offset-sm-4">
			<button type="submit" class="btn btn-success" />{{ lang['send_pass'] }}</button>
		</div>
	</div>
</form>

<script type="text/javascript">
	function reload_captcha() {
		var captc = document.getElementById('img_captcha');
		if (captc != null) {
			captc.src = "{{admin_url}}/captcha.php?rand="+Math.random();
		}
	} 
</script>
[/TWIG]