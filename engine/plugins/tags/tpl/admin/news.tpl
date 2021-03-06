<div class="form-group">
	<label class="col-sm-3 control-label">Список тегов</label>
	<div class="col-sm-9">
		<input id="pTags" name="tags" value="{{ tags }}" autocomplete="off" class="form-control" />
		<span id="suggestLoader" style="width: 20px; visibility: hidden;"></span>
	</div>
</div>

<script type="text/javascript">
$(function() {
	// INIT NEW SUGGEST LIBRARY [ call only after full document load ]
	var aSuggest = new ngSuggest('pTags', {
		'localPrefix': '{{ localPrefix }}',
		'reqMethodName': 'plugin.tags.suggest',
		'lId': 'suggestLoader',
		'hlr': 'true',
		'stCols': 2,
		'stColsClass': [ 'cleft', 'cright' ],
		'stColsHLR': [ true, false ],
		'listDelimiter': ',',
	});
});
</script>
