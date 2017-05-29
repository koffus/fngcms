<h3 class="widget-title">Чат-бокс[selfwin] <small><a target="_blank" href="{link_selfwin}" title="New window"><i class="fa fa-external-link"></i></a></small>[/selfwin]</h3>

<!-- SCRIPTS INTERNALS BEGIN ((( DO NOT CHANGE ))) -->
[:include jchat.script.header.js]
<!-- SCRIPTS INTERNALS END -->

<div class="chat-table" onclick="jchatProcessAreaClick(event);">
	<table id="jChatTable" class="table">
		<tr>
			<td><i class="fa fa-spinner fa-pulse"></i> {l_loading}</td>
		</tr>
	</table>
</div>

[post-enabled]
<form method="post" name="jChatForm" id="jChatForm" onsubmit="chatSubmitForm(); return false;">

	[not-logged]
	<div class="form-group">
		<input type="text" name="name" value="{l_jchat:input.username}" onfocus="if(!jChatInputUsernameDefault){this.value='';jChatInputUsernameDefault=1;}" class="form-control" />
	</div>
	[/not-logged]

	<div class="form-group chat-textarea">
		<textarea id="jChatText" name="text" rows="3" onfocus="jchatCalculateMaxLen(this,'jchatWLen', {maxlen});" onkeyup="jchatCalculateMaxLen(this,'jchatWLen', {maxlen});" class="form-control"></textarea>
		<span id="jchatWLen">{maxlen}</span>
	</div>

	<div class="form-group">
		<input id="jChatSubmit" type="submit" value="{l_jchat:button.post}" class="btn btn-primary form-control" />
	</div>
</form>
[/post-enabled]

<!-- SCRIPTS INTERNALS BEGIN ((( DO NOT CHANGE ))) -->
[:include jchat.script.footer.js]
<!-- SCRIPTS INTERNALS END -->

