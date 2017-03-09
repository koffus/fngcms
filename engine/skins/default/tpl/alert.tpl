<script>
document.addEventListener('DOMContentLoaded', function() {
	$.notify({
		// options
		title: '<b>{title}</b><br />',
		message: '{message}',
	},{
		// settings
		type: '{type}',
	});
});
</script>

<noscript>
	<div id="alert-{id}" class="alert alert-{type}">
		<b>{title} </b><br />{message}
	</div>
</noscript>