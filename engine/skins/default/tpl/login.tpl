<!DOCTYPE html>
<html lang="{l_langcode}">
<head>
    <meta charset="{l_encoding}"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />

    <title>{home_title}</title>

    <link href="{scriptLibrary}/fontawesome-4.7.0/fontawesome.css" rel="stylesheet" />
    <link href="{scriptLibrary}/bootstrap-3.3.7/bootstrap.css" rel="stylesheet" />
    <link href="{skins_url}/css/login.css" rel="stylesheet" />
</head>
<body>

    <noscript><div class="alert alert-danger">Внимание! В вашем браузере отключен <b>JavaScript</b><br />Для полноценной работы с админ. панелью <b>включите его</b></div></noscript>

    <div class="container-fluid">
        <div class="row-fluid" >
            <div class="col-md-offset-4 col-md-4" id="box">
                <h2>{home_title}</h2>
                <hr>
                <form name="login" action="admin.php" method="post" class="form-horizontal">
                    <input type="hidden" name="redirect" value="{redirect}">
                    <input type="hidden" name="action" value="login">

                    <fieldset>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input type="text" name="username" id="username" placeholder="{l_name}" class="form-control" required />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                    <input type="password" name="password" id="password" placeholder="{l_password}" class="form-control" required />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-md btn-login pull-right" data-loading-text="Login …">{l_login}</button>
                            </div>
                        </div>
                        <p class="copyright">2008-{year} © <a href="http://ngcms.ru" target="_blank">Next Generation CMS</a></p>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
