[TWIG]
<div class="block-title">{{ lang['lostpassword_title'] }}</div>
<form name="lostpassword" action="{{ form_action }}" method="post">
<input type="hidden" name="type" value="send" />
	{{entries}}
	[captcha]
		<div class="label label-table captcha pull-left">
			<label>{{ lang['captcha'] }}:</label>
			<input type="text" name="captcha" class="input">
			<img id="img_captcha" src="{{ captcha_url }}?rand={{ captcha_rand }}" alt="captcha" class="captcha" />
			<div class="label-desc">{{ lang['captcha_desc'] }}</div>
		</div>
		<div class="clearfix"></div>
		<div class="label">
			<input type="submit" value="{{ lang['send_pass'] }}" class="button">
		</div>
	[/captcha]
</form>
[/TWIG]