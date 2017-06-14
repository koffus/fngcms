<script>
	var newLineIndex = 1;

	function createVLine(vid) {
		var x = document.getElementById('vlist_'+vid);
		var nr = x.insertRow(x.rows.length-1);
		nr.insertCell(-1).innerHTML = '<input name="viname_'+vid+'_'+newLineIndex+'" value="" type="text" class="form-control" />';

		var cell = nr.insertCell(-1);
		cell.innerHTML = '0 <b>=&gt;</b>';

		var cell = nr.insertCell(-1);
		cell.style.textAlign = 'right';
		cell.innerHTML = '<input name="vicount_'+vid+'_'+newLineIndex+'" type="text" class="form-control" />';

		cell = nr.insertCell(-1);
		cell.innerHTML = '<input name="viactive_'+vid+'_'+newLineIndex+'" type=checkbox value="1" />';

		cell = nr.insertCell(-1);
		cell.innerHTML = '<input name="videl_'+vid+'_'+newLineIndex+'" type=checkbox value="1" />';

		newLineIndex++;
	}
</script>

