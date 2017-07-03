<!DOCTYPE html>
<html lang="{l_langcode}">
<head>
	<title>Next Generation CMS &copy; jChat plugin</title>
	<meta charset="{l_encoding}" />
	<meta http-equiv="content-language" content="{l_langcode}" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="generator" content="NGCMS jChat plugin" />
	<meta name="document-state" content="dynamic" />
	<link href="{jchat.self.css}" rel="stylesheet" type="text/css" media="screen" />
	<script src="{scriptLibrary}/js/jquery.min.js"></script>
	<script src="{scriptLibrary}/functions.js"></script>
	<script src="{scriptLibrary}/ajax.js"></script>
</head>

<body>
<section class="section">
	<h1>Чат-бокс</h1>

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
			<textarea id="jChatText" name="text" rows="8" onfocus="jchatCalculateMaxLen(this,'jchatWLen', {maxlen});" onkeyup="jchatCalculateMaxLen(this,'jchatWLen', {maxlen});" class="form-control"></textarea>
			<span id="jchatWLen">{maxlen}</span>
		</div>

		<div class="form-group">
			<input id="jChatSubmit" type="submit" value="{l_jchat:button.post}" class="btn btn-primary" />
		</div>
	</form>
	[/post-enabled]

	<!-- SCRIPTS INTERNALS BEGIN ((( DO NOT CHANGE ))) -->
	[:include jchat.script.footer.js]
	<!-- SCRIPTS INTERNALS END -->
</section>
</body>
</html>
