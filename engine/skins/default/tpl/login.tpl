<!DOCTYPE html>
<html lang="{l_langcode}">
<head>
	<meta charset="{l_encoding}">
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">

	<title>{home_title}</title>

	<link href="{skins_url}/assets/css/fontawesome.css" rel="stylesheet">
	<link href="{skins_url}/assets/css/themes/default.css" rel="stylesheet">
	<link href="{skins_url}/css/login.css" rel="stylesheet">
</head>
<body>

	<noscript><div class="alert alert-danger">Внимание! В вашем браузере отключен <b>JavaScript</b><br />Для полноценной работы с админ. панелью <b>включите его</b></div></noscript>

	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<!--Login block-->
				<div class="well">
					<form name="login" method="post" action="admin.php">
						<input type="hidden" name="redirect" value="{redirect}">
						<input type="hidden" name="action" value="login">
						<div class="form-group">
							<label for="login" class="control-label">{l_name}</label>
							<input type="text" class="form-control" id="username" name="username" required>
						</div>
						<div class="form-group">
							<label for="password" class="control-label">{l_password}</label>
							<input type="password" class="form-control" id="password" name="password" required>
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-success btn-block" data-loading-text="Login …">{l_login}</button>
						</div>
						<p class="copyright">2008-{year} © <a href="http://ngcms.ru" target="_blank">Next Generation CMS</a></p>
					</form>
				</div>
				<!--/Login block-->
			</div>
		</div>
	</div>
</body>
</html>
