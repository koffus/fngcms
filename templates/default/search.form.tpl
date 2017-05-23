<div class="widget widget-search">
	<form action="{{ form_url }}" method="GET" class="form-inline">
		<input type="hidden" name="category" value="" />
		<input type="hidden" name="postdate" value="" />

		<div class="input-group">
			<input type="text" name="search" placeholder="{{ lang['search.enter'] }}" class="form-control" />
			<span class="input-group-btn">
				<input type="submit" name="submit" value="{{ lang['search.submit'] }}" class="btn btn-outline-primary" />
			</span>
		</div>
	</form>
</div>