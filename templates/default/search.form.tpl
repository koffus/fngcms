<form action="{{ form_url }}" method="GET" class="form-inline justify-content-end">
	<input type="hidden" name="category" value="" />
	<input type="hidden" name="postdate" value="" />

	<div class="input-group">
		<input type="text" name="search" placeholder="{{ lang['search.enter'] }}" class="form-control" />
		<span class="input-group-btn">
			<button type="submit" name="submit" value="{{ lang['search.submit'] }}" class="btn btn-outline-secondary"><i class="fa fa-search"></i></button>
		</span>
	</div>
</form>