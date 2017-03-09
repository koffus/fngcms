<script>
document.addEventListener('DOMContentLoaded', function() {
	$.notify({
		// options
		title: '<b>{title}</b>',
		message: '{message}',
	},{
		// settings
		type: '{type}',
	});
});
</script>

<noscript>
	<div id="alert-{id}" class="alert alert-{type}" role="alert">
		<b>{title} </b> {message}
	</div>
</noscript>