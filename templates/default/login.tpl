<h2 class="section-title">{l_login.title}</h2>

[error]<div class="alert alert-danger">{l_login.error}</div>[/error]
[banned]<div class="alert alert-info">{l_login.banned}</div>[/banned]
[need.activate]<div class="alert alert-info">{l_login.need.activate}</div>[/need.activate]

<div class="row">
	<div class="col-sm-6">
		<div class="card card-block">

		<form name="login" method="post" action="{form_action}">
			<input type="hidden" name="redirect" value="{redirect}"/>

			<div class="form-group">
				<label for="username">{l_login.username}</label>
				<input type="text" id="username" name="username" class="form-control" />
			</div>
			<div class="form-group">
				<label for="password">{l_login.password}</label>
				<input type="password" name="password" class="form-control" />
			</div>

			<div class="form-group row">
				<div class="col-sm-6">
					<a href="{home}/lostpassword/">{l_login.lostpassword}</a>
				</div>
				<div class="col-sm-6">
					<input type="submit" value="{l_login.submit}" class="btn btn-success form-control">
				</div>
			</div>

		</form>
		</div>
	</div>
	<div class="col-sm-6">
		<p class="lead">Зарегистрируйтесь сейчас <span class="text-success">бесплатно</span></p>
		<ul class="list-unstyled card-block">
			<li><b class="text-success">✔</b> See all your orders</li>
			<li><b class="text-success">✔</b> Fast re-order</li>
			<li><b class="text-success">✔</b> Save your favorites</li>
			<li><b class="text-success">✔</b> Fast checkout</li>
			<li><b class="text-success">✔</b> Get a gift <small>(only new customers)</small></li>
		</ul>
		<p><a href="{home}/register/" class="btn btn-info btn-block">Зарегистрируйтесь сейчас</a></p>
	</div>
</div>