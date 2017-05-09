<table class="table table-condensed table-bordered">
	{% for image in images %}
	<tr>
		<td>{{image.number}}</td>
		{% if image.flags.exist %}
		<td>
			<input type="text" name="xfields_{{image.id}}_dscr[{{image.image.id}}]" placeholder="Введите описание.." value="{{ image.description }}" class="form-control" />
			<a href="{{ image.image.url }}" target="_blank">
				{% if image.flags.preview %}
					<img src="{{ image.preview.url }}" width="{{image.preview.width}}" height="{{image.preview.height}}" />
				{% else %}
					NO PREVIEW
				{% endif %}
			</a>
			<br/>
			<label>
				<input type="checkbox" value="1" name="xfields_{{image.id}}_del[{{image.image.id}}]" /> удалить
			</label>
		</td>
		{% else %}
			<td colspan="2">
				<input type="text" name="xfields_{{image.id}}_adscr[]" placeholder="Введите описание.." value="{{ image.description }}" class="form-control" />
				<div class="btn btn-default btn-fileinput">
					<span><i class="fa fa-plus"></i> Add files ...</span>
					<input type="file" name="xfields_{{image.id}}[]" onchange="validateFile(this);">
				</div>
			</td>
		{% endif %}
	</tr>
	{% endfor %}
</table>