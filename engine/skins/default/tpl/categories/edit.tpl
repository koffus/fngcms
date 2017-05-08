<!-- Navigation bar -->
<ul class="breadcrumb">
	<li><a href="admin.php">{{ lang['home'] }}</a></li>
	<li><a href="admin.php?mod=categories">{{ lang['categories_title'] }}</a></li>
	<li class="active">{{ lang['editing'] }} <b>{{ name }}</b></li>
</ul>

<!-- Info content -->
<div class="page-main">
	<form action="admin.php?mod=categories" method="post" enctype="multipart/form-data" class="form-horizontal">
		<input type="hidden" name="token" value="{{ token }}" />
		<input type="hidden" name="action" value="doedit" />
		<input type="hidden" name="catid" value="{{ catid }}" />

		<div class="form-group">
			<div class="col-sm-5">{{ lang['show_main'] }}</div>
			<div class="col-sm-7">
				<label class="btn btn-default form-control">
					<input type="checkbox" autocomplete="off" id="cat_show" name="cat_show" value="1" {% if flags.showInMenu %}checked="checked" {% endif %}>
				</label>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['parent'] }}</div>
			<div class="col-sm-7">
				{{ parent }}
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['title'] }}</div>
			<div class="col-sm-7">
				<input type="text" name="name" value="{{ name }}" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['alt_name'] }}</div>
			<div class="col-sm-7">
				<input type="text" name="alt" value="{{ alt }}" class="form-control" />
			</div>
		</div>
		{% if (flags.haveMeta) %}
		<div class="form-group">
			<div class="col-sm-5">{{ lang['cat_desc'] }}</div>
			<div class="col-sm-7">
				<input type="text" name="description" value="{{ description }}" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['cat_keys'] }}</div>
			<div class="col-sm-7">
				<input type="text" name="keywords" value="{{ keywords }}" class="form-control" />
			</div>
		</div>
		{% endif %}
		<div class="form-group">
			<div class="col-sm-5">{{ lang['cat_number'] }}</div>
			<div class="col-sm-7">
				<input type="text" name="description" value="{{ number }}" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['show.link'] }}</div>
			<div class="col-sm-7">
				<select name="show_link" class="form-control">{{ show_link }}</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['cat_tpl'] }}</div>
			<div class="col-sm-7">
				<select name="tpl" class="form-control">{{ tpl_list }}</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['template_mode'] }}</div>
			<div class="col-sm-7">
				<select name="template_mode" class="form-control">{{ template_mode }}</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['icon'] }}<span class="help-block">{{ lang['icon#desc'] }}</span></div>
			<div class="col-sm-7">
				<input type="text" size="40" name="icon" value="{{ icon }}" maxlength="255" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['attached_icon'] }}<span class="help-block">{{ lang['attached_icon#desc'] }}</span></div>
			<div class="col-sm-7">
				{% if flags.haveAttach %}
				<div class="row">
					<div class="col-sm-6">
						<div id="previewImage" class="text-center form-group"><img src="{{ attach_url }}"/></div>
					</div>
					<div class="col-sm-6">
						<label for="image_del"class="btn btn-danger form-control">
							<div class="text-left"> <input type="checkbox" autocomplete="off" id="image_del" name="image_del" value="1"> {{ lang['delete_icon'] }}</div>
						</label>
						<div class="btn btn-default btn-fileinput form-control">
							<span>{{ lang['attach.new'] }}</span>
							<input type="file" name="image" id="image" onchange="validateFile(this);">
						</div>
					</div>
				</div>
				{% else %}
				<div class="btn btn-default btn-fileinput form-control">
					<span>{{ lang['attach.new'] }}</span>
					<input type="file" name="image" id="image" onchange="validateFile(this);">
				</div>
				{% endif %}
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['alt_url'] }}</div>
			<div class="col-sm-7">
				<input type="text" name="alt_url" value="{{ alt_url }}" class="form-control" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['orderby'] }}</div>
			<div class="col-sm-7">
				{{ orderlist }}
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-5">{{ lang['category.info'] }}<span class="help-block">{{ lang['category.info#desc'] }}</span></div>
			<div class="col-sm-7">
				<textarea id="info" name="info" rows="5" class="form-control">{{ info }}</textarea>
			</div>
		</div>
		{{ extend }}
		
		<div class="well text-center">
			{% if flags.canModify %}<input type="submit" value="{{ lang['save'] }}" class="btn btn-success">{% endif %}
		</div>
	</form>
</div>
