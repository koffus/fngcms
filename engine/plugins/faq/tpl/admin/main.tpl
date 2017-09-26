<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang.home }}</a></li>
	<li><a href="admin.php?mod=extras" title="{{ lang.extras }}">{{ lang.extras }}</a></li>
	<li><a href="admin.php?mod=extra-config&plugin=faq" title="faq">Вопросы и ответы</a></li>
	<li class="active">{{ action }} {{ id }}</li>
</ul>

<!-- Info content -->
<div class="page-main">

		{{ entries }}

</div>