<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang.home }}</a></li>
	<li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
	{% if (flags.haveForm) %}
			<li><a href="admin.php?mod=extra-config&plugin=feedback">feedback</a></li>
		{% if (flags.haveField) %}
			<li><a href="?mod=extra-config&plugin=feedback&action=form&id={{ formID }}">Форма "{{ formName }}"</a></li>
			<li class="active">Поле "{{ fieldName }}"</li>
		{% elseif (flags.addField) %}
			<li><a href="?mod=extra-config&plugin=feedback&action=form&id={{ formID }}">Форма "{{ formName }}"</a></li>
			<li class="active">Добавление нового поля</li>
		{% else %}
			<li class="active">Форма "{{ formName }}"</li>
		{% endif %}
	{% else %}
		<li class="active">feedback</li>
	{% endif %}
</ul>