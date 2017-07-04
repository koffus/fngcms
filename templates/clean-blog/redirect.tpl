<!DOCTYPE html>
<html lang="{l_langcode}">
<head>
	<title>{title}</title>
	<meta charset="{l_encoding}" />
	<meta http-equiv="content-language" content="{l_langcode}" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="Document-State" content="dynamic" />
	<style type="text/css">
		body {
			background: #fff;
			color: #444;
			font-family: -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
			font-size: 1rem;
			margin: 5em auto;
			padding: 0;
			width: 60%;
			max-width: 480px;
			-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
			box-shadow: 0 1px 3px rgba(0,0,0,0.13);
			border: 1px solid #bcdff1;
			border-radius: .25rem;
		}
		.alert {
			background: #d9edf7;
			padding: 1em 2em;
		}
		p {
			line-height: 1;
			margin: 20px 0;
			color: #31708f;
		}
		a {
			color: #245269;
			font-weight: 500;
		}
	</style>
</head>
<body>
	<div class="alert" role="alert">
		<p>{message}</p>
		<p><a href="{link}">{linktext}</a></p>
	</div>
</body>
</html>
