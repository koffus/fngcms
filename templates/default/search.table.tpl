<h2 class="page-header">{{ lang['search.site_search'] }}</h2>

<form method="GET" action="{{ form_url }}">
	<fieldset>
		<div class="form-group">
			<div class="input-group">
				<input type="text" name="search" value="{{ search }}" placeholder="{{ lang['search.enter'] }}" class="form-control" />
				<span class="input-group-btn">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default" data-toggle="collapse" data-target="#searchSettings">
							<input type="checkbox" autocomplete="off" onchange="setCookie('searchSettings',this.checked?1:0);" {{ searchSettings }} /> <i class="fa fa-sliders"></i>
						</label>
					</div>
					<button id="submit" type="submit" name="submit" class="btn btn-default" title="{{ lang['search.submit'] }}"><i class="fa fa-search"></i></button>
				</span>
			</div>
		</div>

		<div id="searchSettings" class="row collapse {% if searchSettings %}in{% endif %}">
			<div class="col-sm-6">
				<div class="form-group">
					<label>{{ lang['search.filter.category'] }}</label>  
					<div>
						<div class="search_catz">{{ catlist }}</div>
					</div>
				</div>
				<div class="form-group">
					<label>{{ lang['search.filter.date'] }}</label>  
					<div>
						<select name="postdate" class="form-control"><option value="">Любая</option>{{ datelist }}</select>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label>{{ lang['search.filter.orderlist'] }}</label>  
					<div>
						<select name="orderby" class="form-control">{{ orderlist }}</select>
					</div>
				</div>
				<div class="form-group">
					<label>{{ lang['search.filter.author'] }}</label>  
					<div>
						<input type="text" name="author" value="{{ author }}" class="form-control" />
					</div>
				</div>
			</div>
		</div>
	<fieldset>
</form>

{% if flags.notfound %}
	<p class="alert alert-info">{{ lang['search.notfound'] }}</p>
{% endif %}
{% if flags.error %}
	<p class="alert alert-danger">{{ lang['search.error'] }}</p>
{% endif %}

{% if flags.found %}
	<p class="alert alert-success">{{ lang['search.found'] }}: <b>{{ count }}</b></p>
	<section class="section">
		<ul class="media-list">
		{% for entry in data %}
			{{ entry }}
		{% endfor %}
		</ul>
	</section>
{% endif %}

{{ pagination }}