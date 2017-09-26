<html>
<head>
	<title>BixBite CMS Runtime error: <?php echo $title; ?></title>
	<meta charset="UTF-8"/>
	<style>
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
	</style>
</head>
	<body>
	<div id="hdrSpanItem"></div>
	<script>
		var i = 0;
		var cnt = 0;
		while (i < document.body.childNodes.length) {
			var node = document.body.childNodes[i];
			if (node.tagName == 'DIV') {
				document.body.removeChild(document.body.childNodes[i]);
				break;
			}
			if ((node.tagName == 'TITLE')||(node.tagName == 'STYLE')) {
				i++;
			} else {
				document.body.removeChild(document.body.childNodes[i]);
			}
		}
	</script>

	<?php
		print "<h1>BixBite CMS Software generated fatal error: ".$title."</h1>\n";
		print "<div class='dmsg'>[ Software error ]: ".$title."</div><br/>";
		if ($description) {
			print "<p><i>".$description."</i></p>";
		}
		print "<h2>Stack trace</h2>";
		print "<table class='dtrace'><thead><td>Line #</td><td>Function</td><td>File name</td></tr></thead><tbody>";

		$trace = debug_backtrace();
		$num = 0;
		foreach ($trace as $k => $v) {
			$num++;
			print "<tr><td>".$v['line']."</td><td>".$v['function']."<td>".$v['file']."</td></tr>";
			if ($num > 3) {
				print "<tr><td colspan='3'>...</td></tr>";
				break;
			}
		}
		print "</tbody></table></body></html>";
		exit;
