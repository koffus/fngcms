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
	<div id="alert-{id}" class="alert alert-{type} alert-dismissible fade show" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
		<b>{title} </b> {message}
	</div>
</noscript>