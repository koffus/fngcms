<h2 class="section-title">{l_login.title}</h2>

[error]<div class="alert alert-danger">{l_login.error}</div>[/error]
[banned]<div class="alert alert-info">{l_login.banned}</div>[/banned]
[need.activate]<div class="alert alert-info">{l_login.need.activate}</div>[/need.activate]

<form name="login" method="post" action="{form_action}">
    <input type="hidden" name="redirect" value="{redirect}"/>

    <div class="form-group row">
        <label for="username" class="col-sm-4 col-form-label">{l_login.username}</label>
        <div class="col-sm-8">
            <input type="text" id="username" name="username" class="form-control" />
        </div>
    </div>
    <div class="form-group row">
        <label for="password" class="col-sm-4 col-form-label">{l_login.password}</label>
        <div class="col-sm-8">
            <input type="password" name="password" class="form-control" />
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-4 offset-sm-4">
            <button type="submit" class="btn btn-success">{l_login.submit}</button>
        </div>
        <div class="col-sm-4 text-right">
            <small><a href="{home}/register/">{l_login.register}</a></small>
            <br>
            <small><a href="{home}/lostpassword/">{l_login.lostpassword}</a></small>
        </div>
    </div>
</form>