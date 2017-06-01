<?php
	$lastError = error_get_last();

	// Activate only for fatal errors
	$flagFatal = 0;

	switch ($lastError['type']) {
		case E_ERROR:
		case E_PARSE:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
			$flagFatal = 1;
			break;
	}

	if (!$flagFatal)
		return true;

	print "<div id=\"ngErrorInformer\">";
	print "<h1>NGCMS Runtime error: ".$lastError['message']."</h1>\n";
	print "<div class='dmsg'>[".$lastError['type']."]: ".$lastError['message']."</div><br/>";
	print "<h2>Stack trace</h2>";
	print "<table class='dtrace'><thead><td>Line #</td><td>File name</td></tr></thead><tbody>";
	print "<tr><td>".$lastError['line']."</td><td>".$lastError['file']."</td></tr></tbody></table>";
	print '<style type="text/css">
					body {
						font: 1em Georgia,"Times New Roman",serif;
					}
					.dmsg {
						border: 1px #EEEEEE solid;
						padding: 10px;
						background-color: yellow;
					}
					.dtrace TBODY TD {
						padding: 3px;
						/*border: 1px #EEEEEE solid;*/
						background-color: #EEEEEE;
					}
					.dtrace THEAD TD {
						padding: 3px;
						background-color: #EEEEEE;
						font-weight: bold;
					}
				</style>';
	print '</div>';
?>

<div id="hdrSpanItem"></div>
<script>
	var xc = document.getElementById('ngErrorInformer').innerHTML;
	var i = 0;
	var cnt = 0;
	while (i < document.body.childNodes.length) {
		var node = document.body.childNodes[i];
		if (node.tagName == 'DIV') {
			document.body.removeChild(document.body.childNodes[i]);
			break;
		}
		if ((node.tagName == 'TITLE')||(node.tagName == 'STYLE')||(node.tagName == '')) {
			i++;
		} else {
			document.body.removeChild(document.body.childNodes[i]);
		}
	}
	document.body.innerHTML = xc;
</script>

<?php
	return false;
