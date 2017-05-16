<script type="text/javascript">

    $( document ).ready(function() {
        var registrationValidator = (function() {

            var validateFields = function() {

                $("#reg_login").change(function() {

                    if ($('#reg_login').val() == '') {
                        $("#reg_login").css({
                            "display": "table-cell",
                            "background": "#f9f9f9",
                            "border": "1px solid #e2e2e2",
                            "box-shadow": "inset 2px 3px 3px -2px #e2e2e2"
                        });
                        $("small#reg_login").html("{{ lang.auth_login_descr }}");
                        return;
                    }

                    $.post('{{ home }}/engine/rpc.php', { json : 1, methodName : 'core.registration.checkParams', rndval: new Date().getTime(), params : json_encode({ 'login' : $('#reg_login').val() }), dataType: 'json' }, function(data) {
                        if(typeof data == 'string') {
                            resTX = $.parseJSON(data);
                        } else {
                            resTX = data;
                        }
                        if (!resTX['status']) {
                            alert('Error ['+resTX['errorCode']+']: '+resTX['errorText']);
                        } else {
                            if ((resTX['data']['login']>0)&&(resTX['data']['login'] < 100)) {
                                $("#reg_login").css("border-color", "#b54d4b");
                                $("small#reg_login").html("<span style='color:#b54d4b;'>{{ lang.theme.registration_msg_login_warning }}</span>");
                            } else {
                                $("#reg_login").css("border-color", "#94c37a");
                                $("small#reg_login").html("<span style='color:#94c37a;'>{{ lang.theme.registration_msg_login_success }}</span>");
                            }
                        }
                    }, "text").error(function() {
                        alert('HTTP error during request', 'ERROR');
                    });

                });

                $("#reg_email").change(function() {

                    if ($('#reg_email').val() == '') {
                        $("#reg_email").css({
                            "display": "table-cell",
                            "background": "#f9f9f9",
                            "border": "1px solid #e2e2e2",
                            "box-shadow": "inset 2px 3px 3px -2px #e2e2e2"
                        });
                        $("small#reg_email").html("<span>{{ lang.auth_email_descr }}</span>");
                        return;
                    }

                    $.post('{{ home }}/engine/rpc.php', { json : 1, methodName : 'core.registration.checkParams', rndval: new Date().getTime(), params : json_encode({ 'email' : $('#reg_email').val() }), dataType: 'json' }, function(data) {
                        if(typeof data == 'string') {
                            resTX = $.parseJSON(data);
                        } else {
                            resTX = data;
                        }
                        if (!resTX['status']) {
                            alert('Error ['+resTX['errorCode']+']: '+resTX['errorText']);
                        } else {
                            if ((resTX['data']['email']>0)&&(resTX['data']['email'] < 100)) {
                                $("#reg_email").css("border-color", "#b54d4b");
                                $("small#reg_email").html("<span style='color:#b54d4b;'>{{ lang.theme.registration_msg_email_warning }}</span>");
                            } else {
                                $("#reg_email").css("border-color", "#94c37a");
                                $("small#reg_email").html("<span style='color:#94c37a;'>{{ lang.theme.registration_msg_email_success }}</span>");
                            }
                        }
                    }).error(function() {
                        alert('HTTP error during request', 'ERROR');
                    });

                });


                $("#reg_password2").change(function() {

                    if ($('#reg_password2').val() == '' && $('#reg_password').val() == '') {
                        $("#reg_password").css({
                            "display": "table-cell",
                            "background": "#f9f9f9",
                            "border": "1px solid #e2e2e2",
                            "box-shadow": "inset 2px 3px 3px -2px #e2e2e2"
                        });
                        $("#reg_password2").css({
                            "display": "table-cell",
                            "background": "#f9f9f9",
                            "border": "1px solid #e2e2e2",
                            "box-shadow": "inset 2px 3px 3px -2px #e2e2e2"
                        });
                        $("small#reg_password2").html("<span>{{ lang.auth_pass2_descr }}</span>");
                        return;
                    }

                    if ($('#reg_password2').val() != $('#reg_password').val()) {
                        $("#reg_password").css("border-color", "#b54d4b");
                        $("#reg_password2").css("border-color", "#b54d4b");
                        $("small#reg_password2").html("<span style='color:#b54d4b;'>{{ lang.theme.registration_msg_password_warning }}</span>");
                    } else {
                        $("#reg_password").css("border-color", "#94c37a");
                        $("#reg_password2").css("border-color", "#94c37a");
                        $("small#reg_password2").html("<span style='color:#94c37a;'>{{ lang.theme.registration_msg_password_success }}</span>");
                    }


                });


                $("#reg_password").change(function() {

                    if ($('#reg_password2').val() == '' && $('#reg_password').val() == '') {
                        $("#reg_password").css({
                            "display": "table-cell",
                            "background": "#f9f9f9",
                            "border": "1px solid #e2e2e2",
                            "box-shadow": "inset 2px 3px 3px -2px #e2e2e2"
                        });
                        $("#reg_password2").css({
                            "display": "table-cell",
                            "background": "#f9f9f9",
                            "border": "1px solid #e2e2e2",
                            "box-shadow": "inset 2px 3px 3px -2px #e2e2e2"
                        });
                        $("small#reg_password2").html("<span>{{ lang.auth_pass2_descr }}</span>");
                        return;
                    }
                    if ($('#reg_password2').val() != $('#reg_password').val()) {
                        $("#reg_password").css("border-color", "#b54d4b");
                        $("#reg_password2").css("border-color", "#b54d4b");
                        $("small#reg_password2").html("<span style='color:#b54d4b;'>{{ lang.theme.registration_msg_password_warning }}</span>");
                    } else {
                        $("#reg_password").css("border-color", "#94c37a");
                        $("#reg_password2").css("border-color", "#94c37a");
                        $("small#reg_password2").html("<span style='color:#94c37a;'>{{ lang.theme.registration_msg_password_success }}</span>");
                    }


                });

            };

            return {
                validateFields: validateFields
            };

        })();

        registrationValidator.validateFields();
    });

</script>
<!-- Page Header -->
<header class="intro-header" style="background-image: url('{tpl_url}/img/home-bg.jpg')">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
				<div class="post-heading">
					<h1>{{ lang.registration }}</h1>
					<hr class="small">
					<span class="subheading">{{ lang.registration }}</span>
				</div>
			</div>
		</div>
	</div>
</header>

<!-- Page Content -->
<div class="container">
	<div class="row">
		<div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">

			<form name="register" action="{{ form_action }}" method="post" onsubmit="return validate();">
				<input type="hidden" name="type" value="doregister" />

				<fieldset>
					{% for entry in entries %}
						{% if entry.type == 'input' %}
						<div class="form-group row">
							<label for="{{ entry.id }}" class="col-sm-4 col-form-label">{{ entry.title }}</label>
							<div class="col-sm-8">
								<input id="{{ entry.id }}" type="{{ entry.type }}" name="{{ entry.name }}" value="{{ entry.value }}" class="form-control">
								<small id="{{ entry.id }}">{{ entry.descr }}</small>
							</div>
						</div>
						{% endif %}
						{% if entry.type == 'password' %}
						<div class="form-group row">
							<label for="{{ entry.id }}" class="col-sm-4 col-form-label">{{ entry.title }}</label>
							<div class="col-sm-8">
								<input id="{{ entry.id }}" type="{{ entry.type }}" name="{{ entry.name }}" value="{{ entry.value }}" class="form-control">
								<small id="{{ entry.id }}">{{ entry.descr }}</small>
							</div>
						</div>
						{% endif %}
						{% if entry.type == 'text' %}
						<div class="form-group row">
							<label for="{{ entry.id }}" class="col-sm-4 col-form-label">{{ entry.title }}</label>
							<div class="col-sm-8">
								<textarea name="{{ entry.name }}" class="form-control" rows="5"></textarea>
							</div>
						</div>
						{% endif %}
						{% if entry.type == 'select' %}
							<div class="form-group">
								<label for="{{ entry.id }}" class="col-sm-4 col-form-label">{{ entry.title }}</label>
								<div class="col-sm-8">
									<select class="form-control" type="{{ entry.type }}" name="{{ entry.name }}">{% for key,value in entry.values %}<option value="{{ key }}">{{ value }}</option>{% endfor %}</select>
								</div>
							</div>
						{% endif %}
					{% endfor %}
					{% if flags.hasCaptcha %}
						<div class="form-group row">
							<label for="vcode" class="col-sm-4 col-form-label">{{lang.theme['captcha_title'] }}</label>
							<div class="col-sm-8">
								<div class="input-group">
									<input type="text" name="vcode" id="vcode" class="form-control" required />
									<span class="input-group-addon p-0">
										<img src="{{ admin_url }}/captcha.php" id="img_captcha" onclick="reload_captcha();" alt="Security code" class="captcha"/>
									</span>
								</div>
								<small id="{{ entry.id }}">{{ lang.theme.captcha_code }}</small>
							</div>
						</div>
					{% endif %}
					<div class="form-group">
						<div class="col-sm-8 offset-sm-4">
							<p class="checkbox"><label><input type="checkbox" name="agree" /> {{lang.theme['registration_rules'] }}</label></p>
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-sm-8 offset-sm-4">
							<button type="submit" class="btn btn-success" />{{ lang.register }}</button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	function validate() {
		if (document.register.agree.checked == false) {
			window.alert('{{ lang.theme['registration_check_rules'] }}');
			return false;
		}
		return true;
	}
	function reload_captcha() {
		var captc = document.getElementById('img_captcha');
		if (captc != null) {
			captc.src = "{{ admin_url }}/captcha.php?rand=" + Math.random();
		}
	}
</script>