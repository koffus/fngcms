{% if (entriesCount > 0) %}
	<tr class="xfImagesList">
		<td colspan="2"><legend>{{ fieldTitle }} <small>({{ entriesCount }})</small></legend>
		{% for entry in entries %}
			{% if entry.flags.hasPreview %}
				<a target="_blank" href="{{ entry.url }}" title="{{ entry.description }}">
					<img alt="{{ entry.description}}" src="{{ entry.purl }}" width="{{ entry.pwidth }}" height="{{ entry.pheight }}" class="img-thumbnail" />
				</a>
			{% else %}
				<a target="_blank" href="{{ entry.url }}">{{ entry.origName }} ({{ entry.description }})</a>
			{% endif %}
		{% endfor %}
		</td>
	</tr>
{% endif %}
